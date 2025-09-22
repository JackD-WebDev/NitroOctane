<script setup lang="ts">
import { createZodPlugin } from '@formkit/zod';

const { t, locale } = useI18n();
definePageMeta({
  title: 'Login',
  middleware: ['guest']
});
useAppTitle(t('navbar.login'));

const authStore = useAuthStore();

const route = useRoute();
const passwordResetSuccess = computed(() => route.query.reset === 'success');

const showTwoFactorChallenge = ref(false);
const twoFactorSubmitting = ref(false);
const twoFactorError = ref('');

const loginSchema = createLoginSchema();

const [zodPlugin, submitHandler] = createZodPlugin(
  loginSchema,
  async (formData) => {
    authStore.error = '';
    showTwoFactorChallenge.value = false;

    try {
      const result = await authStore.logIn(formData, locale.value);

      if (result.requiresTwoFactor) {
        showTwoFactorChallenge.value = true;
        twoFactorError.value = '';
      } else if (result.user) {
        const localizedNavigate = useLocalizedNavigate();
        await localizedNavigate('/account');
      }
    } catch (error: unknown) {
      if (error instanceof Error) {
        authStore.error = error.message;
      } else {
        authStore.error = t('login.network_error');
      }
    }
  }
);

const onSubmitTwoFactorCode = async (code: string) => {
  twoFactorSubmitting.value = true;
  twoFactorError.value = '';

  try {
    const user = await authStore.completeTwoFactorLogin(code);
    if (user) {
      const localizedNavigate = useLocalizedNavigate();
      await localizedNavigate('/account');
    }
  } catch (error: unknown) {
    if (error instanceof Error) {
      twoFactorError.value = error.message;
    } else {
      twoFactorError.value = t(
        'login.2fa_error',
        'Two-factor authentication failed'
      );
    }
  } finally {
    twoFactorSubmitting.value = false;
  }
};

const onSubmitRecoveryCode = async (recoveryCode: string) => {
  twoFactorSubmitting.value = true;
  twoFactorError.value = '';

  try {
    const user = await authStore.completeTwoFactorLogin('', recoveryCode);
    if (user) {
      const localizedNavigate = useLocalizedNavigate();
      await localizedNavigate('/account');
    }
  } catch (error: unknown) {
    if (error instanceof Error) {
      twoFactorError.value = error.message;
    } else {
      twoFactorError.value = t(
        'login.recovery_error',
        'Recovery code verification failed'
      );
    }
  } finally {
    twoFactorSubmitting.value = false;
  }
};
</script>

<template>
  <div>
    <div v-if="passwordResetSuccess" class="reset-success">
      {{ t('login.password_reset_success') }}
    </div>

    <div v-if="showTwoFactorChallenge" class="two-factor-container">
      <TwoFactorChallenge
        :submitting="twoFactorSubmitting"
        :error="twoFactorError"
        @submit-code="onSubmitTwoFactorCode"
        @submit-recovery="onSubmitRecoveryCode"
      />

      <div class="back-to-login">
        <button
          type="button"
          class="btn-link"
          @click="showTwoFactorChallenge = false"
        >
          ← Back to login
        </button>
      </div>
    </div>

    <div v-else>
      <h2>{{ t('navbar.login').toUpperCase() }}</h2>
      <FormKit
        type="form"
        :plugins="[zodPlugin]"
        :actions="false"
        @submit="submitHandler"
      >
        <div v-if="authStore.error" class="error">
          {{ authStore.error }}
        </div>
        <FormKit
          type="email"
          name="email"
          :label="t('register.email').toUpperCase()"
          validation="required|email"
          :placeholder="t('register.email')"
        />
        <FormKit
          type="password"
          name="password"
          :label="t('register.password').toUpperCase()"
          validation="required|length:8"
          :placeholder="t('register.password')"
          style="margin-bottom: 1.5rem"
        />

        <div class="remember-row" style="margin-bottom: 1rem">
          <FormKit
            type="checkbox"
            name="remember"
            :label="t('login.remember_me').toUpperCase()"
          />
        </div>
        <div class="actions-row">
          <NuxtLink to="/forgot-password" class="forgot-link">
            {{ t('login.forgot_password').toUpperCase() }}
          </NuxtLink>
          <FormKit type="submit">{{ t('navbar.login').toUpperCase() }}</FormKit>
        </div>
      </FormKit>
    </div>
  </div>
</template>

<style scoped>
.error {
  text-transform: uppercase;
  color: red;
  background-color: #ffe6e6;
  padding: 8px;
  border: 1px solid #ffcccc;
  border-radius: 0.4rem;
  margin-bottom: 1rem;
}

.reset-success {
  text-transform: uppercase;
  background: #edfbea;
  border: 1px solid #c2e8bc;
  color: #265e29;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1.25rem;
  font-size: 1rem;
  max-width: 42rem;
}

form {
  max-width: 44rem;
}

.two-factor-container {
  max-width: 50rem;
  margin: 0 auto;
}

.back-to-login {
  text-align: center;
  margin-top: 2rem;
}

.btn-link {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  font-size: 0.9rem;
  text-decoration: underline;
  padding: 0.5rem;
}

.btn-link:hover {
  color: #0056b3;
}

.actions-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.forgot-link {
  font-size: 1.6rem;
}

.forgot-link:hover {
  color: #0056b3;
}
</style>
