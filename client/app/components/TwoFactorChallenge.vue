<script setup lang="ts">
interface Props {
  submitting?: boolean;
  error?: string;
}

const props = withDefaults(defineProps<Props>(), {
  submitting: false,
  error: ''
});

const emit = defineEmits<{
  'submit-code': [code: string];
  'submit-recovery': [recoveryCode: string];
}>();

const recoveryCode = ref('');
const showRecoveryInput = ref(false);

const digits = ref<string[]>(Array(6).fill(''));
const inputRefs = Array.from({ length: 6 }).map(() =>
  ref<HTMLInputElement | null>(null)
);

const isValidCode = computed(
  () =>
    digits.value.join('').length === 6 && /^\d{6}$/.test(digits.value.join(''))
);

const setInputRef = (
  el: Element | ComponentPublicInstance | null,
  idx: number
) => {
  if (!el) return;
  const node = el as HTMLInputElement;
  if (!node || typeof node.value === 'undefined') return;
  if (!inputRefs[idx]) return;
  inputRefs[idx].value = node;
};

const focusInput = (idx: number) => {
  const inputRef = inputRefs[idx];
  if (!inputRef) return;
  const node = inputRef.value;
  if (node && typeof node.focus === 'function') node.focus();
};

const onDigitInput = (idx: number, event: Event) => {
  const target = event.target as HTMLInputElement;
  const val = (target.value || '').replace(/\D/g, '').slice(0, 1);
  digits.value[idx] = val;
  if (val && idx < 5) focusInput(idx + 1);
};

const onDigitKeydown = (idx: number, event: KeyboardEvent) => {
  const key = event.key;
  if (key === 'Backspace') {
    if (digits.value[idx]) {
      digits.value[idx] = '';
      event.preventDefault();
    } else if (idx > 0) {
      focusInput(idx - 1);
      event.preventDefault();
    }
  } else if (key === 'ArrowLeft' && idx > 0) {
    focusInput(idx - 1);
    event.preventDefault();
  } else if (key === 'ArrowRight' && idx < 5) {
    focusInput(idx + 1);
    event.preventDefault();
  }
};

const onPasteDigits = (event: ClipboardEvent) => {
  const paste = event.clipboardData?.getData('text') || '';
  const only = paste.replace(/\D/g, '').slice(0, 6).split('');
  for (let i = 0; i < 6; i++) {
    digits.value[i] = only[i] ?? '';
  }
  const firstEmpty = digits.value.findIndex((d) => !d);
  if (firstEmpty === -1) focusInput(5);
  else focusInput(Math.max(0, firstEmpty - 1));
};

const authCodeFromDigits = () => digits.value.join('');

const onSubmitCode = () => {
  if (isValidCode.value && !props.submitting) {
    emit('submit-code', authCodeFromDigits());
  } else {
    triggerShake();
  }
};

const isShaking = ref(false);
const triggerShake = () => {
  isShaking.value = true;
  setTimeout(() => {
    isShaking.value = false;
  }, 600);
};

const onSubmitRecovery = () => {
  if (recoveryCode.value.trim() && !props.submitting) {
    emit('submit-recovery', recoveryCode.value.trim());
  }
};

onMounted(() => {
  nextTick(() => {
    const first = inputRefs[0]?.value;
    if (first && typeof first.focus === 'function') first.focus();
  });
});

watch(
  () => props.error,
  (val) => {
    if (val) triggerShake();
  }
);
</script>

<template>
  <div class="two-factor-challenge">
    <div class="challenge-header">
      <h3>Two-Factor Authentication Required</h3>
      <p>Enter the 6-digit code from your authenticator app</p>
    </div>

    <form
      :class="['challenge-form', { invalid: isShaking }]"
      @submit.prevent="onSubmitCode"
    >
      <div class="code-input-group">
        <div class="otp-input" @paste.prevent="onPasteDigits($event)">
          <template v-for="i in 6" :key="i - 1">
            <input
              :ref="(el) => setInputRef(el, i - 1)"
              class="otp-box"
              type="text"
              inputmode="numeric"
              pattern="[0-9]*"
              maxlength="1"
              autocomplete="one-time-code"
              :disabled="submitting"
              :value="digits[i - 1]"
              @input="(e) => onDigitInput(i - 1, e)"
              @keydown="(e) => onDigitKeydown(i - 1, e)"
            />
          </template>
        </div>
      </div>

      <div class="challenge-actions">
        <button
          type="submit"
          class="btn-primary"
          :disabled="!isValidCode || submitting"
        >
          {{ submitting ? 'Verifying...' : 'Verify Code' }}
        </button>
      </div>

      <div v-if="error" class="error">{{ error }}</div>
    </form>

    <div class="recovery-option">
      <button
        v-if="!showRecoveryInput"
        type="button"
        class="btn-link"
        @click="showRecoveryInput = true"
      >
        Use a recovery code instead
      </button>

      <div v-else class="recovery-form">
        <p>Enter one of your recovery codes:</p>
        <input
          v-model="recoveryCode"
          type="text"
          placeholder="Recovery code"
          class="recovery-input"
          :disabled="submitting"
        />
        <div class="recovery-actions">
          <button
            type="button"
            class="btn-primary"
            :disabled="!recoveryCode.trim() || submitting"
            @click="onSubmitRecovery"
          >
            {{ submitting ? 'Verifying...' : 'Use Recovery Code' }}
          </button>
          <button
            type="button"
            class="btn-link"
            @click="showRecoveryInput = false"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.two-factor-challenge {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
  background: #3ca767;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.challenge-header {
  text-align: center;
  margin-bottom: 2rem;
}

.challenge-header h3 {
  color: #333;
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
}

.challenge-header p {
  color: #666;
  margin: 0;
  font-size: 0.9rem;
}

.challenge-form {
  margin-bottom: 2rem;
}

.code-input-group {
  margin-bottom: 1.5rem;
}

.otp-input {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin: 0.5rem 0 1.25rem 0;
}

.otp-box {
  width: 3.5rem;
  height: 3.5rem;
  font-size: 1.4rem;
  text-align: center;
  border: 2px solid rgba(255, 255, 255, 0.65);
  border-radius: 8px;
  background: #2f8e56;
  color: #fff;
  padding: 0.25rem 0.5rem;
  font-family: monospace;
  box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.06);
  transition: border-color 0.15s, box-shadow 0.15s, transform 0.08s;
}

.otp-box:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.95);
  box-shadow: 0 0 0 3px rgba(47, 142, 86, 0.12);
  transform: translateY(-1px);
}

.challenge-form.invalid .otp-box {
  border-color: #e55353 !important;
  box-shadow: 0 0 0 3px rgba(229, 83, 83, 0.08) !important;
}

@keyframes shake {
  0% {
    transform: translateX(0);
  }
  20% {
    transform: translateX(-8px);
  }
  40% {
    transform: translateX(8px);
  }
  60% {
    transform: translateX(-6px);
  }
  80% {
    transform: translateX(6px);
  }
  100% {
    transform: translateX(0);
  }
}

.challenge-form.invalid .otp-input {
  animation: shake 0.6s ease-in-out;
}

.code-input {
  width: 100%;
  padding: 1rem;
  font-size: 1.5rem;
  text-align: center;
  border: 2px solid #ddd;
  border-radius: 6px;
  letter-spacing: 0.5em;
  font-family: monospace;
  background: #fff;
  transition: border-color 0.2s;
}

.code-input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.code-input:disabled {
  background: #f8f9fa;
  color: #666;
}

.challenge-actions {
  text-align: center;
}

.btn-primary {
  background: #0a84bb;
  color: white;
  border: none;
  padding: 0.6rem 2.25rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  transition: background-color 0.2s, transform 0.06s;
  max-width: 520px;
  width: 60%;
  margin: 0 auto;
  display: block;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #dc3545;
  margin-top: 1rem;
  padding: 0.75rem;
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
  text-align: center;
  font-size: 0.9rem;
}

.recovery-option {
  text-align: center;
  border-top: 1px solid #eee;
  padding-top: 1.5rem;
}

.btn-link {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  font-size: 0.9rem;
  text-decoration: underline;
  padding: 0;
}

.btn-link:hover {
  color: #0056b3;
}

.recovery-form {
  margin-top: 1rem;
}

.recovery-form p {
  color: #666;
  margin: 0 0 1rem 0;
  font-size: 0.9rem;
}

.recovery-input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #ddd;
  border-radius: 4px;
  margin-bottom: 1rem;
  font-family: monospace;
  text-align: center;
}

.recovery-input:focus {
  outline: none;
  border-color: #007bff;
}

.recovery-actions {
  display: flex;
  gap: 0.5rem;
  flex-direction: column;
}

.recovery-actions .btn-primary {
  margin-bottom: 0.5rem;
}

@media (min-width: 480px) {
  .recovery-actions {
    flex-direction: row;
    justify-content: center;
  }

  .recovery-actions .btn-primary {
    margin-bottom: 0;
    width: auto;
    flex: 1;
  }
}
</style>
