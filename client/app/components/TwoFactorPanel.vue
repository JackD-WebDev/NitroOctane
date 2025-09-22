<template>
  <aside class="two-factor-panel">
    <h3>{{ t('two_factor.title') }}</h3>

    <div v-if="loading || checkingStatus" class="loading-state">
      <p>Loading...</p>
    </div>

    <div v-else-if="!twoFactorEnabled">
      <p>{{ t('two_factor.description') }}</p>

      <div v-if="!confirmed">
        <p>{{ t('two_factor.confirm_password_prompt') }}</p>
        <input
          v-model="password"
          type="password"
          placeholder="Current password"
        />
        <button
          :disabled="confirming || !password"
          style="margin-left: 2rem"
          @click="onConfirm"
        >
          Confirm
        </button>
        <div v-if="confirmError" class="error">{{ confirmError }}</div>
      </div>

      <div v-else>
        <p v-if="activating">
          {{ t('two_factor.activating') || 'Enabling 2FA, please wait...' }}
        </p>
        <p v-else>
          {{ t('two_factor.enabled_message') }}
        </p>

        <div
          v-if="qr || secret || (recoveryCodes && recoveryCodes.length)"
          class="qr-card"
        >
          <TwoFactorSVG v-if="qr" :svg-content="qr" />

          <div v-if="secret" class="secret-row">
            <p>
              <strong>Secret:</strong> <code>{{ secret }}</code>
            </p>
          </div>

          <div
            v-if="recoveryCodes && recoveryCodes.length"
            class="recovery-codes"
          >
            <h4>Recovery Codes</h4>
            <ul>
              <li v-for="c in recoveryCodes" :key="c">{{ c }}</li>
            </ul>
            <div class="setup-complete-actions">
              <button class="btn-primary" @click="completeSetup">
                {{ t('two_factor.save_recovery_complete') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      <p>
        {{ t('two_factor.enabled_message') }}
      </p>

      <div v-if="!showDisableConfirm" class="enabled-actions">
        <button
          class="btn-secondary"
          @click="showRecoveryCodes = !showRecoveryCodes"
        >
          {{
            showRecoveryCodes
              ? t('two_factor.hide') || 'Hide'
              : t('two_factor.view_recovery_codes')
          }}
        </button>
        <button class="btn-danger" @click="showDisableConfirm = true">
          {{ t('two_factor.disable_2fa') }}
        </button>
      </div>

      <div v-if="showRecoveryCodes" class="recovery-codes-section">
        <div v-if="loadingRecoveryCodes">Loading recovery codes...</div>
        <div v-else-if="recoveryCodes && recoveryCodes.length" class="qr-card">
          <div class="recovery-codes">
            <h4>Your Recovery Codes</h4>
            <ul>
              <li v-for="c in recoveryCodes" :key="c">{{ c }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="showDisableConfirm" class="disable-confirm">
        <div class="warning-box">
          <h4>⚠️ {{ t('two_factor.disable_confirm_title') }}</h4>
          <p>{{ t('two_factor.disable_confirm_text') }}</p>
          <p>{{ t('two_factor.confirm_password_prompt') }}</p>

          <input
            v-model="disablePassword"
            type="password"
            :placeholder="t('account.current_password')"
          />

          <div class="confirm-actions">
            <button
              class="btn-danger"
              :disabled="disabling || !disablePassword"
              @click="onDisable"
            >
              {{
                disabling
                  ? t('two_factor.disabling') || 'Disabling...'
                  : t('two_factor.disable_button')
              }}
            </button>
            <button class="btn-secondary" @click="cancelDisable">
              {{ t('two_factor.disable_cancel') }}
            </button>
          </div>

          <div v-if="disableError" class="error">{{ disableError }}</div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
const twoFactor = useTwoFactor();
const { t } = useI18n();

const password = ref('');
const confirming = ref(false);
const confirmed = ref(false);
const confirmError = ref('');
const activating = ref(false);

const twoFactorEnabled = ref(false);
const checkingStatus = ref(true);
const showDisableConfirm = ref(false);
const showRecoveryCodes = ref(false);
const loadingRecoveryCodes = ref(false);
const disablePassword = ref('');
const disabling = ref(false);
const disableError = ref('');

const qr = computed<string | null>(() => {
  const v = twoFactor.qrSvg as unknown as { value?: string | null };
  return v?.value ?? null;
});
const secret = computed<string | null>(() => {
  const v = twoFactor.secret as unknown as { value?: string | null };
  return v?.value ?? null;
});
const recoveryCodes = computed<string[]>(() => {
  const v = twoFactor.recoveryCodes as unknown as { value?: string[] | null };
  return v?.value ?? [];
});
const loading = computed<boolean>(() => {
  const v = twoFactor.loading as unknown as { value?: boolean };
  return v?.value ?? false;
});

onMounted(async () => {
  checkingStatus.value = true;
  try {
    const status = await twoFactor.getStatus();
    twoFactorEnabled.value = status.enabled;
  } catch (err) {
    console.error('Failed to check 2FA status:', err);
    twoFactorEnabled.value = false;
  } finally {
    checkingStatus.value = false;
  }
});

const onConfirm = async () => {
  confirming.value = true;
  confirmError.value = '';
  try {
    const res = await twoFactor.confirmPassword(password.value);
    if (res.success) {
      confirmed.value = true;
    } else {
      confirmError.value = res.message || 'Password confirmation failed';
    }
  } catch (err) {
    confirmError.value = 'Password confirmation failed';
    console.error('confirmPassword error:', err);
  } finally {
    confirming.value = false;
  }
};

watch(
  () => confirmed.value,
  async (val) => {
    if (val) {
      activating.value = true;
      try {
        await twoFactor.activate(password.value);
        await twoFactor.fetchQr();
        await twoFactor.fetchRecoveryCodes();
      } catch (err) {
        console.error('2FA activation failed:', err);
      } finally {
        activating.value = false;
      }
    }
  }
);

const completeSetup = () => {
  twoFactorEnabled.value = true;
};

const onDisable = async () => {
  disabling.value = true;
  disableError.value = '';
  try {
    const confirmRes = await twoFactor.confirmPassword(disablePassword.value);
    if (!confirmRes.success) {
      disableError.value = confirmRes.message || 'Password confirmation failed';
      return;
    }

    const res = await twoFactor.disable();
    if (res.success) {
      twoFactorEnabled.value = false;

      showDisableConfirm.value = false;
      showRecoveryCodes.value = false;
      disablePassword.value = '';

      confirmed.value = false;
      password.value = '';
      activating.value = false;
      confirmError.value = '';
    } else {
      disableError.value = res.message || '2FA disable failed';
    }
  } catch (err) {
    disableError.value = '2FA disable failed';
    console.error('2FA disable error:', err);
  } finally {
    disabling.value = false;
  }
};

const cancelDisable = () => {
  showDisableConfirm.value = false;
  disablePassword.value = '';
  disableError.value = '';
};

watch(
  () => showRecoveryCodes.value,
  async (val) => {
    if (
      val &&
      twoFactorEnabled.value &&
      (!recoveryCodes.value || recoveryCodes.value.length === 0)
    ) {
      loadingRecoveryCodes.value = true;
      try {
        await twoFactor.fetchRecoveryCodes();
      } catch (err) {
        console.error('Failed to fetch recovery codes:', err);
      } finally {
        loadingRecoveryCodes.value = false;
      }
    }
  }
);
</script>

<style scoped>
.two-factor-panel {
  border-left: 1px solid #eee;
  padding-left: 1rem;
}

.loading-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error {
  color: #dc3545;
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
}

.enabled-actions {
  display: flex;
  gap: 0.5rem;
  margin: 1rem 0;
  flex-wrap: wrap;
}

.btn-secondary {
  background: #6c757d;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-secondary:hover {
  background: #545b62;
}

.btn-danger {
  background: #dc3545;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-danger:hover {
  background: #c82333;
}

.btn-danger:disabled,
.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.recovery-codes-section {
  margin: 1rem 0;
}

.disable-confirm {
  margin: 1rem 0;
}

.warning-box {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 6px;
  padding: 1.5rem;
  margin: 1rem 0;
}

.warning-box h4 {
  color: #856404;
  margin: 0 0 1rem 0;
}

.warning-box p {
  color: #856404;
  margin: 0.5rem 0;
}

.confirm-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.qr-card {
  background: #fff;
  border: 1px solid #e6e6e6;
  padding: 1rem;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  max-width: 320px;
  color: #333;
}

.qr-card .recovery-codes {
  background: #f9f9f9;
  padding: 0.5rem;
  border-radius: 4px;
  margin-top: 1rem;
}

.qr-card .recovery-codes h4 {
  color: #333;
  margin: 0 0 0.5rem 0;
}

.qr-card .recovery-codes ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.qr-card .recovery-codes li {
  background: #fff;
  color: #333;
  padding: 0.25rem 0.5rem;
  margin: 0.25rem 0;
  border-radius: 3px;
  border: 1px solid #ddd;
  font-family: monospace;
  font-size: 0.9em;
}

.setup-complete-actions {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #ddd;
  text-align: center;
}

.btn-primary {
  background: #28a745;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
}

.btn-primary:hover {
  background: #218838;
}

.qr-card .secret-row {
  background: #f9f9f9;
  padding: 0.5rem;
  border-radius: 4px;
  margin: 1rem 0;
}

.qr-card .secret-row p {
  margin: 0;
  color: #333;
}

.qr-card .secret-row code {
  background: #fff;
  color: #333;
  padding: 0.2rem 0.4rem;
  border-radius: 3px;
  border: 1px solid #ddd;
}

input[type='password'] {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin: 0.5rem 0;
  width: 100%;
  max-width: 250px;
}

button {
  background: #007bff;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  margin: 0.5rem 0.5rem 0.5rem 0;
}

button:hover:not(:disabled) {
  background: #0056b3;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
