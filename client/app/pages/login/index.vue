<script setup lang="ts">
const { t, locale } = useI18n();
definePageMeta({
  title: 'Login',
  middleware: ['guest']
});
useAppTitle(t('navbar.login'));

const authStore = useAuthStore();

const email = ref('');
const password = ref('');

const fieldName = {
  email: t('register.email'),
  password: t('register.password')
};

const credentials = computed(() => ({
  email: email.value,
  password: password.value
}));

const clearForm = () => {
  email.value = '';
  password.value = '';
};

const login = async () => {
  authStore.error = '';

  try {
    const user = await authStore.logIn(credentials.value, locale.value);
    if (user) {
      clearForm();
      const localizedNavigate = useLocalizedNavigate();
      await localizedNavigate('/account');
    }
  } catch (e: unknown) {
    if (e instanceof Error) {
      authStore.error = e.message;
    } else {
      authStore.error = 'Network error';
    }
  }
};
</script>

<template>
  <div>
    <h2>{{ t('navbar.login').toUpperCase() }}</h2>
    <form @submit.prevent="login">
      <div v-if="authStore.error" class="error">
        {{ authStore.error }}
      </div>
      <label for="email">{{ fieldName.email }}</label>
      <input
        id="email"
        v-model="email"
        type="email"
        :placeholder="fieldName.email"
      />
      <label for="password">{{ fieldName.password }}</label>
      <input
        id="password"
        v-model="password"
        type="password"
        :placeholder="fieldName.password"
      />
      <button type="submit">{{ t('navbar.login') }}</button>
    </form>
  </div>
</template>

<style scoped>
form {
  display: flex;
  flex-direction: column;
  max-width: 400px;
}

.error {
  color: red;
  background-color: #ffe6e6;
  padding: 8px;
  border: 1px solid #ffcccc;
  border-radius: 0.4rem;
  margin-bottom: 1rem;
}

label {
  margin-top: 1.2rem;
}

input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
}

button {
  margin-top: 3.8rem;
  padding: 8px;
  background-color: #00ff08b8;
  color: white;
  border: 1px solid #ddffdeb8;
  border-radius: 0.4rem;
  cursor: pointer;
}

button:hover {
  background-color: #009c06;
}
</style>
