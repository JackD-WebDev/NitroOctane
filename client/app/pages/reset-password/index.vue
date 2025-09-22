<script setup lang="ts">
import { createZodPlugin } from '@formkit/zod';

definePageMeta({
  title: 'Reset Password',
  middleware: ['guest', 'unverified']
});

const { t } = useI18n();
useAppTitle(t('login.reset_password', 'Reset Password'));

const route = useRoute();
const token = computed(() => String(route.query.token || ''));
const emailParam = computed(() => String(route.query.email || ''));

const schema = createResetPasswordSchema();

const submitting = ref(false);
const errorMessage = ref('');

const authStore = useAuthStore();
const [zodPlugin, submitHandler] = createZodPlugin(schema, async (formData) => {
  submitting.value = true;
  errorMessage.value = '';
  try {
    await authStore.resetPassword(formData);
    const localizedNavigate = useLocalizedNavigate();
    await localizedNavigate('/login?reset=success');
  } catch (error: unknown) {
    if (error instanceof Error) errorMessage.value = error.message;
    else errorMessage.value = t('login.reset_failed', 'Reset failed');
  } finally {
    submitting.value = false;
  }
});
</script>

<template>
  <div class="reset-wrapper">
    <h2>{{ t('login.reset_password').toUpperCase() }}</h2>
    <FormKit
      type="form"
      :plugins="[zodPlugin]"
      :actions="false"
      :disabled="submitting"
      @submit="submitHandler"
    >
      <div v-if="errorMessage" class="error">{{ errorMessage }}</div>

      <FormKit type="hidden" name="token" :value="token" />
      <FormKit type="hidden" name="email" :value="emailParam" />

      <FormKit
        type="password"
        name="password"
        :label="t('register.password').toUpperCase()"
        validation="required|length:12"
        :placeholder="t('register.password')"
      />
      <FormKit
        type="password"
        name="password_confirmation"
        :label="t('register.confirm_password').toUpperCase()"
        validation="required|length:12"
        :placeholder="t('register.confirm_password')"
        style="margin-bottom: 1.25rem"
      />
      <FormKit type="submit">{{ t('login.reset_password') }}</FormKit>
    </FormKit>

    <div class="back-link">
      <NuxtLink to="/login">{{ t('login.back_to_login') }}</NuxtLink>
    </div>
  </div>
</template>

<style scoped>
.reset-wrapper {
  max-width: 460px;
}
.error {
  color: red;
  background: #ffe6e6;
  padding: 0.5rem 0.75rem;
  margin-bottom: 1rem;
  border: 1px solid #ffb3b3;
  border-radius: 4px;
}
.back-link {
  margin-top: 2rem;
  font-size: 1rem;
}
</style>
