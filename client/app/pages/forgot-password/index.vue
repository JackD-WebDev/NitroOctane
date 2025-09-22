<script setup lang="ts">
import { createZodPlugin } from '@formkit/zod';

definePageMeta({
  title: 'Forgot Password',
  middleware: ['guest', 'unverified']
});

const { t } = useI18n();
useAppTitle(t('login.forgot_password'));

const submitted = ref(false);
const authStore = useAuthStore();

const schema = createForgotPasswordSchema();
const [zodPlugin, submitHandler] = createZodPlugin(schema, async (formData) => {
  try {
    await authStore.forgotPassword(formData.email);
  } catch {
    submitted.value = false;
  } finally {
    submitted.value = true;
  }
});
</script>

<template>
  <div class="forgot-wrapper">
    <h2>{{ t('login.forgot_password') }}</h2>
    <p v-if="!submitted" class="hint">
      {{ t('login.forgot_password_hint') }}
    </p>
    <p v-else class="hint success">
      {{ t('login.forgot_password_submitted') }}
    </p>

    <FormKit
      v-if="!submitted"
      type="form"
      :plugins="[zodPlugin]"
      :actions="false"
      @submit="submitHandler"
    >
      <FormKit
        type="email"
        name="email"
        :label="t('register.email')"
        validation="required|email"
        :placeholder="t('register.email')"
        style="margin-bottom: 1.25rem"
      />
      <FormKit type="submit">{{
        t('login.send_reset_link').toUpperCase()
      }}</FormKit>
    </FormKit>

    <div class="back-link">
      <NuxtLink to="/login">{{ t('login.back_to_login') }}</NuxtLink>
    </div>
  </div>
</template>

<style scoped>
a {
  text-transform: uppercase;
  color: inherit;
}
.forgot-wrapper {
  max-width: 460px;
  text-transform: uppercase;
}
.hint {
  font-size: 0.85rem;
  margin-bottom: 1.6rem;
  text-transform: uppercase;
}
.success {
  color: #1e6022;
}
.back-link {
  margin-top: 2rem;
  font-size: 1.6rem;
  text-transform: uppercase;
}
</style>
