<script setup lang="ts">
import { ZodError } from 'zod';

const { t, locale } = useI18n();
const authStore = useAuthStore();
const registerSchema = await createRegisterSchema();

definePageMeta({
  title: 'Register',
  middleware: ['guest']
});
useAppTitle(t('navbar.register'));

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
      const errs = error.errors || [];
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
    await localizedNavigate('/account');
  } catch (error: unknown) {
    if (error instanceof Error) {
      authStore.error = error.message;
    } else {
      authStore.error = t('register.network_error');
    }
  }
};

type UniqueField = 'username' | 'email';

const uniqueChecks = reactive({
  username: { loading: false, unique: null as boolean | null, message: '' },
  email: { loading: false, unique: null as boolean | null, message: '' }
});

const fieldErrors = reactive<Record<string, string>>({
  firstname: '',
  middlename: '',
  lastname: '',
  username: '',
  email: '',
  password: '',
  confirmPassword: ''
});

const formState = reactive<Register>({
  firstname: '',
  middlename: '',
  lastname: '',
  username: '',
  email: '',
  password: '',
  confirmPassword: ''
});

const timers: Record<UniqueField, number | null> = {
  username: null,
  email: null
};

const runUniquenessCheck = async (field: UniqueField, value: string) => {
  if (!value) {
    uniqueChecks[field].unique = null;
    uniqueChecks[field].message = '';
    return;
  }

  uniqueChecks[field].loading = true;
  try {
    const response = await useApi<UniqueCheckResponse>(
      `check-unique?field=${field}&value=${encodeURIComponent(value)}`
    );
    if (response && typeof response.unique === 'boolean') {
      uniqueChecks[field].unique = response.unique;
      uniqueChecks[field].message = response.unique
        ? ''
        : t(`register.validation.${field}_taken`);
    } else {
      uniqueChecks[field].unique = null;
      uniqueChecks[field].message = '';
    }
  } catch {
    uniqueChecks[field].message = t('register.network_error');
  } finally {
    uniqueChecks[field].loading = false;
  }
};

const scheduleUniquenessCheck = (field: UniqueField, value: string) => {
  if (timers[field]) {
    clearTimeout(timers[field]!);
  }
  timers[field] = window.setTimeout(
    () => runUniquenessCheck(field, value),
    800
  );
};

const onFieldInput = (field: UniqueField, val: unknown) => {
  if (fieldErrors[field]) fieldErrors[field] = '';
  scheduleUniquenessCheck(field, String(val || ''));
};

const onFieldBlur = (field: UniqueField, val: unknown) => {
  if (timers[field]) {
    clearTimeout(timers[field]!);
    timers[field] = null;
  }
  runUniquenessCheck(field, String(val || ''));
};

const handleUsernameInput = (val: unknown) => onFieldInput('username', val);
const handleUsernameBlur = (val: unknown) => onFieldBlur('username', val);
const handleEmailInput = (val: unknown) => onFieldInput('email', val);
const handleEmailBlur = (val: unknown) => onFieldBlur('email', val);

const clearFieldError = (field: keyof typeof fieldErrors) => {
  fieldErrors[field] = '';
};
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
        @input="handleUsernameInput"
        @blur="handleUsernameBlur"
      />
      <div class="field-feedback">
        <small v-if="uniqueChecks.username.loading"
          >{{ t('checking') }}...</small
        >
        <small
          v-else-if="uniqueChecks.username.unique === false"
          class="text-small-uppercase-error"
          >{{ uniqueChecks.username.message }}</small
        >
        <small
          v-else-if="uniqueChecks.username.unique === true"
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
        @input="handleEmailInput"
        @blur="handleEmailBlur"
      />
      <div class="field-feedback">
        <small v-if="uniqueChecks.email.loading">{{ t('checking') }}...</small>
        <small
          v-else-if="uniqueChecks.email.unique === false"
          class="text-small-uppercase-error"
          >{{ uniqueChecks.email.message }}</small
        >
        <small
          v-else-if="uniqueChecks.email.unique === true"
          class="text-small-uppercase-success"
          >{{ t('register.email_available') }}</small
        >
      </div>
      <FormKit
        type="password"
        name="password"
        :label="t('register.password').toUpperCase()"
        :placeholder="t('register.password')"
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
      <div v-if="authStore.error" style="color: red; margin-top: 1rem">
        {{ authStore.error }}
      </div>
    </FormKit>
  </div>
</template>

<style scoped>
form {
  max-width: 40rem;
}
.field-feedback {
  margin-top: 0.25rem;
  margin-bottom: 0.5rem;
}
</style>
