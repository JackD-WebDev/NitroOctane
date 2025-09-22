<script setup lang="ts">
definePageMeta({
  title: 'Verify Email',
  middleware: ['guest', 'unverified'],
  layout: 'blank'
});

const { t } = useI18n();
useAppTitle(t('verify.title', 'Verify Email'));

const route = useRoute();
const authStore = useAuthStore();

const verifying = ref(false);
const verified = ref(false);

const error = ref('');
const resendLoading = ref(false);
const resendSuccess = ref(false);
const resendError = ref('');

onMounted(async () => {
  const { id, hash, expires, signature } = route.query;
  if (id && hash && expires && signature) {
    await verifyEmail();
  }
});

const verifyEmail = async () => {
  verifying.value = true;
  error.value = '';
  try {
    const { id, hash, expires, signature } = route.query as Record<
      string,
      string
    >;

    const qsParams: Record<string, string> = {};
    if (expires) qsParams.expires = String(expires);
    if (signature) qsParams.signature = String(signature);

    const qs = new URLSearchParams(qsParams).toString();
    const endpoint =
      id && hash
        ? `email/verify/${id}/${hash}${qs ? `?${qs}` : ''}`
        : 'email/verify';
    const response = await useApi(endpoint, {
      method: 'GET'
    });
    if (
      response &&
      typeof response === 'object' &&
      'success' in response &&
      response.success
    ) {
      verified.value = true;
      await authStore.fetchUser(true);
      await authStore.refreshVerification?.();
      // Notify opener (if this page was opened from the app via target="_blank")
      try {
        const msg = { type: 'nitro:email_verified', verified: true };
        if (window.opener && !window.opener.closed) {
          // Post message to opener to allow it to refresh state or navigate
          try {
            window.opener.postMessage(msg, window.location.origin);
          } catch {
            // ignore
          }
        }
      } catch {
        // ignore
      }
    }
  } catch (err) {
    console.error('Verification error:', err);
    error.value =
      err instanceof Error
        ? err.message
        : t('verify.error', 'Verification failed');
  } finally {
    verifying.value = false;
  }
};

const resendVerification = async () => {
  resendLoading.value = true;
  resendSuccess.value = false;
  resendError.value = '';
  try {
    const email = authStore.user?.email;
    if (!email) {
      resendError.value = t(
        'verify.resend_no_email',
        'No email address found.'
      );
      return;
    }
    const response = await useApi('email/resend-verification', {
      method: 'POST',
      body: JSON.stringify({ email })
    });
    if (
      response &&
      typeof response === 'object' &&
      'success' in response &&
      response.success
    ) {
      resendSuccess.value = true;
    } else {
      resendError.value =
        response && typeof response === 'object' && 'message' in response
          ? (response.message as string)
          : t('verify.resend_failed', 'Resend failed.');
    }
  } catch (err) {
    resendError.value =
      err instanceof Error
        ? err.message
        : t('verify.resend_failed', 'Resend failed.');
  } finally {
    resendLoading.value = false;
  }
};

const closeTab = () => {
  try {
    window.close();
  } catch {
    // ignore
  }
};
</script>

<template>
  <div class="verify-wrapper">
    <h2>{{ t('verify.title').toUpperCase() }}</h2>

    <!-- Verifying state -->
    <div v-if="verifying" class="status verifying">
      <div class="spinner"></div>
      {{ t('verify.verifying', 'Verifying your email address...') }}
    </div>

    <!-- Success state -->
    <div v-else-if="verified" class="status success">
      <div class="success-icon">✓</div>
      <h3>{{ t('verify.success_title', 'Email Verified!') }}</h3>
      <p>
        {{
          t(
            'verify.success_message',
            'Your email address has been successfully verified. You will be redirected to your account shortly.'
          )
        }}
      </p>
      <div class="actions">
        <button class="btn-link" @click="closeTab">
          {{ t('verify.close_tab', 'Close This Tab') }}
        </button>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="status error">
      <div class="error-icon">✗</div>
      <h3>{{ t('verify.error_title', 'Verification Failed') }}</h3>
      <p>{{ error }}</p>
      <p>
        {{
          t(
            'verify.error_help',
            'The verification link may have expired or is invalid. Please try requesting a new verification email.'
          )
        }}
      </p>
      <div class="actions">
        <button
          v-if="authStore.user?.email && !verified"
          class="btn-link"
          :disabled="resendLoading"
          @click="resendVerification"
        >
          {{
            resendLoading
              ? t('verify.resend_loading', 'Resending...')
              : t('verify.resend_button', 'Resend Verification Email')
          }}
        </button>
        <span v-if="resendSuccess" class="resend-success">
          {{ t('verify.resend_success', 'Verification email sent!') }}
        </span>
        <span v-if="resendError" class="resend-error">
          {{ resendError }}
        </span>
        <NuxtLink to="/login" class="btn-link">
          {{ t('verify.back_to_login', 'Back to Login') }}
        </NuxtLink>
      </div>
    </div>

    <!-- No parameters state -->
    <div v-else class="status info">
      <h3>{{ t('verify.no_params_title', 'Email Verification') }}</h3>
      <p>
        {{
          t(
            'verify.no_params_message',
            'Please click the verification link in your email to verify your account.'
          )
        }}
      </p>
      <div class="actions">
        <button
          v-if="authStore.user?.email && !verified"
          class="btn-link"
          :disabled="resendLoading"
          @click="resendVerification"
        >
          {{
            resendLoading
              ? t('verify.resend_loading', 'Resending...')
              : t('verify.resend_button', 'Resend Verification Email')
          }}
        </button>
        <span v-if="resendSuccess" class="resend-success">
          {{ t('verify.resend_success', 'Verification email sent!') }}
        </span>
        <span v-if="resendError" class="resend-error">
          {{ resendError }}
        </span>
        <NuxtLink to="/login" class="btn-link">
          {{ t('verify.back_to_login', 'Back to Login') }}
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.verify-wrapper {
  max-width: 500px;
  margin: 0 auto;
  text-align: center;
}

.status {
  padding: 2rem;
  border-radius: 0.5rem;
  margin: 2rem 0;
}

.verifying {
  background: #1a2b1a;
  border: 1px solid #00ff08b8;
  color: #55ff00;
}

.success {
  background: #edfbea;
  border: 1px solid #c2e8bc;
  color: #265e29;
}

.error {
  background: #ffe6e6;
  border: 1px solid #ffcccc;
  color: #d63031;
}

.info {
  background: #1a2b1a;
  border: 1px solid #00ff08b8;
  color: #55ff00;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 2px solid #55ff00;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

.success-icon,
.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.success-icon {
  color: #27ae60;
}

.error-icon {
  color: #e74c3c;
}

.actions {
  margin-top: 2rem;
}

.btn-link {
  color: #55ff00;
  text-decoration: underline;
  font-size: 1rem;
}

.btn-link:hover {
  color: #27c427;
}

.resend-success {
  display: block;
  color: #27c427;
  margin-top: 1rem;
}
.resend-error {
  display: block;
  color: #e74c3c;
  margin-top: 1rem;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
