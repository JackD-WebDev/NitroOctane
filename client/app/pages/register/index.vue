<script setup lang="ts">
import { ZodError } from 'zod';

const { t, locale } = useI18n();
const authStore = useAuthStore();

definePageMeta({
  title: 'Register',
  middleware: ['guest']
});
useAppTitle(t('navbar.register'));

const fieldErrors = reactive<Record<string, string>>({
  firstname: '',
  middlename: '',
  lastname: '',
  username: '',
  email: '',
  password: '',
  confirmPassword: ''
});

const formState = reactive<Register & { lang?: string }>({
  firstname: '',
  middlename: '',
  lastname: '',
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  lang: locale.value
});

const {
  uniqueChecks,
  focused,
  onFieldInput,
  onFieldFocus,
  onFieldBlur,
  resetFieldState
} = useUniqueFieldCheck();
const registerSchema = await createRegisterSchema();

const registerSchemaWithConfirm = registerSchema.refine(
  (data) => data.password === data.confirmPassword,
  {
    message: t('register.validation.passwords_must_match'),
    path: ['confirmPassword']
  }
);

const submitHandler = async (formData: Register) => {
  authStore.error = '';

  try {
    await registerSchemaWithConfirm.parseAsync(formData);
  } catch (error: unknown) {
    if (error instanceof ZodError) {
      const errs = error.issues ?? [];
      Object.keys(fieldErrors).forEach((k) => (fieldErrors[k] = ''));
      let hadFieldError = false;
      for (const issue of errs) {
        const path = issue.path?.[0] as string | undefined;
        if (path && Object.prototype.hasOwnProperty.call(fieldErrors, path)) {
          fieldErrors[path] = issue.message;
          hadFieldError = true;
        }
      }
      if (!hadFieldError) {
        authStore.error = t('register.validation_failed');
      }
      return;
    }
    authStore.error = t('register.network_error');
    return;
  }

  try {
    await authStore.register(
      {
        ...formData,
        password_confirmation: formData.confirmPassword
      } as unknown as Parameters<typeof authStore.register>[0],
      locale.value
    );
    const localizedNavigate = useLocalizedNavigate();
    await localizedNavigate('/verify-email');
  } catch (error: unknown) {
    if (error instanceof Error) {
      authStore.error = error.message;
    } else {
      authStore.error = t('register.network_error');
    }
  }
};

const clearFieldError = (field: keyof typeof fieldErrors) => {
  fieldErrors[field] = '';
};

onUnmounted(() => {
  resetFieldState('username');
  resetFieldState('email');
});
</script>

<template>
  <div>
    <h2>{{ t('navbar.register').toUpperCase() }}</h2>
    <FormKit type="form" :actions="false" @submit="submitHandler">
      <FormKit
        type="text"
        name="firstname"
        :label="t('register.firstname').toUpperCase()"
        :placeholder="t('register.firstname')"
        validation="required"
        @input="() => clearFieldError('firstname')"
      />
      <div v-if="fieldErrors.firstname" class="text-small-uppercase-error">
        {{ fieldErrors.firstname }}
      </div>
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
        :placeholder="t('register.lastname')"
        validation="required"
        @input="() => clearFieldError('lastname')"
      />
      <div v-if="fieldErrors.lastname" class="text-small-uppercase-error">
        {{ fieldErrors.lastname }}
      </div>
      <FormKit
        v-model="formState.username"
        type="text"
        name="username"
        :label="t('register.username').toUpperCase()"
        :placeholder="t('register.username')"
        validation="required"
        :class="{
          'input-error': uniqueChecks.username.value.unique === false
        }"
        @input="(val: unknown) => onFieldInput('username', val)"
        @focus="() => onFieldFocus('username')"
        @blur="() => onFieldBlur('username')"
      />
      <div class="field-feedback">
        <small
          v-if="
            uniqueChecks.username.value.unique === false &&
            formState.username.length > 3
          "
          class="text-small-uppercase-error"
        >
          {{ uniqueChecks.username.value.message }}
        </small>
        <small
          v-if="
            focused.username &&
            formState.username.length > 3 &&
            uniqueChecks.username.value.loading
          "
          >{{ t('checking') }}...</small
        >
        <small
          v-if="
            focused.username &&
            formState.username.length > 3 &&
            uniqueChecks.username.value.unique === true
          "
          class="text-small-uppercase-success"
          >{{
            t('register.validation.username_available', {
              username: formState.username || ''
            })
          }}</small
        >
      </div>
      <FormKit
        v-model="formState.email"
        type="email"
        name="email"
        :label="t('register.email').toUpperCase()"
        :placeholder="t('register.email')"
        validation="required|email"
        :class="{
          'input-error': uniqueChecks.email.value.unique === false
        }"
        @input="(val: unknown) => onFieldInput('email', val)"
        @focus="() => onFieldFocus('email')"
        @blur="() => onFieldBlur('email')"
      />
      <div class="field-feedback">
        <small
          v-if="
            uniqueChecks.email.value.unique === false &&
            formState.email.length > 3
          "
          class="text-small-uppercase-error"
        >
          {{ uniqueChecks.email.value.message }}
        </small>
        <small
          v-if="
            focused.email &&
            formState.email.length > 3 &&
            uniqueChecks.email.value.loading
          "
          >{{ t('checking') }}...</small
        >
        <small
          v-if="
            focused.email &&
            formState.email.length > 3 &&
            uniqueChecks.email.value.unique === true
          "
          class="text-small-uppercase-success"
          >{{
            t('register.validation.email_available', { email: formState.email })
          }}</small
        >
      </div>
      <FormKit
        type="password"
        name="password"
        :label="t('register.password').toUpperCase()"
        :placeholder="t('register.password')"
        validation="required"
        @input="() => clearFieldError('password')"
      />
      <div v-if="fieldErrors.password" class="text-small-uppercase-error">
        {{ fieldErrors.password }}
      </div>
      <FormKit
        type="password"
        name="confirmPassword"
        :label="t('register.confirm_password').toUpperCase()"
        :placeholder="t('register.confirm_password')"
        validation="required"
        @input="() => clearFieldError('confirmPassword')"
      />
      <div
        v-if="fieldErrors.confirmPassword"
        class="text-small-uppercase-error"
      >
        {{ fieldErrors.confirmPassword }}
      </div>
      <FormKit type="submit" style="margin-top: 2rem">{{
        t('register.submit').toUpperCase()
      }}</FormKit>
      <FormKit v-model="formState.lang" type="hidden" name="lang" />
      <div v-if="authStore.error" class="form-error">
        {{ authStore.error }}
      </div>
    </FormKit>
  </div>
</template>

<style lang="scss" scoped>
form {
  max-width: 40rem;
}
.form-error {
  color: red;
  margin-top: 1rem;
}
.field-feedback {
  margin-top: 0.25rem;
  margin-bottom: 0.5rem;
}
.field-feedback small {
  transition: visibility 0.2s, opacity 0.2s;
}
</style>
