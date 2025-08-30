<script setup lang="ts">
import { createZodPlugin } from '@formkit/zod';

const { t, locale } = useI18n();
definePageMeta({
  title: 'Login',
  middleware: ['guest']
});
useAppTitle(t('navbar.login'));

const authStore = useAuthStore();

const loginSchema = createLoginSchema();

const [zodPlugin, submitHandler] = createZodPlugin(
  loginSchema,
  async (formData) => {
    authStore.error = '';

    try {
      const user = await authStore.logIn(formData, locale.value);
      if (user) {
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
</script>

<template>
  <div>
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
        style="margin-bottom: 2rem"
      />
      <FormKit type="submit">{{ t('navbar.login').toUpperCase() }}</FormKit>
    </FormKit>
  </div>
</template>

<style scoped>
.error {
  color: red;
  background-color: #ffe6e6;
  padding: 8px;
  border: 1px solid #ffcccc;
  border-radius: 0.4rem;
  margin-bottom: 1rem;
}

form {
  max-width: 400px;
}
</style>
