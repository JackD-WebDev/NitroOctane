<script setup lang="ts">
import { createZodPlugin } from '@formkit/zod';

const { t, locale } = useI18n();
const authStore = useAuthStore();

definePageMeta({
  title: 'Register',
  middleware: ['guest']
});
useAppTitle(t('navbar.register'));

const registerSchema = createRegisterSchema();
const registerSchemaWithConfirm = registerSchema.refine(
  (data) => data.password === data.confirmPassword,
  {
    message: t('register.validation.passwords_must_match'),
    path: ['confirmPassword']
  }
);

const [zodPlugin, submitHandler] = createZodPlugin(
  registerSchemaWithConfirm,
  async (formData) => {
    try {
      await authStore.register(
        {
          ...formData,
          password_confirmation: formData.confirmPassword
        },
        locale.value
      );
      const localizedNavigate = useLocalizedNavigate();
      await localizedNavigate('/account');
    } catch (e: unknown) {
      if (e instanceof Error) {
        authStore.error = e.message;
      } else {
        authStore.error = t('register.network_error');
      }
    }
  }
);
</script>

<template>
  <div>
    <h2>{{ t('navbar.register').toUpperCase() }}</h2>
    <FormKit
      type="form"
      :plugins="[zodPlugin]"
      :actions="false"
      @submit="submitHandler"
    >
      <FormKit
        type="text"
        name="firstname"
        :label="t('register.firstname').toUpperCase()"
        validation="required"
        :placeholder="t('register.firstname')"
      />
      <FormKit
        type="text"
        name="middlename"
        :label="t('register.middlename').toUpperCase()"
        :placeholder="t('register.middlename')"
      />
      <FormKit
        type="text"
        name="lastname"
        :label="t('register.lastname').toUpperCase()"
        validation="required"
        :placeholder="t('register.lastname')"
      />
      <FormKit
        type="text"
        name="username"
        :label="t('register.username').toUpperCase()"
        validation="required|length:3"
        :placeholder="t('register.username')"
      />
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
        validation="required|length:6"
        :placeholder="t('register.password')"
      />
      <FormKit
        type="password"
        name="confirmPassword"
        :label="t('register.confirm_password').toUpperCase()"
        validation="required"
        :placeholder="t('register.confirm_password')"
      />
      <FormKit type="submit" style="margin-top: 2rem">{{
        t('register.submit').toUpperCase()
      }}</FormKit>
      <div v-if="authStore.error" style="color: red; margin-top: 1rem">
        {{ authStore.error }}
      </div>
    </FormKit>
  </div>
</template>

<style scoped>
form {
  max-width: 400px;
}
</style>
