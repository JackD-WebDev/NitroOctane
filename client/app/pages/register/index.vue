<script setup lang="ts">
const { t, locale } = useI18n();

definePageMeta({
  title: 'Register',
  middleware: ['guest']
});
useAppTitle(t('navbar.register'));

const firstname = ref('');
const middlename = ref('');
const lastname = ref('');
const email = ref('');
const username = ref('');
const password = ref('');
const confirmPassword = ref('');

const authStore = useAuthStore();

const newUser = computed(() => ({
  email: email.value,
  firstname: firstname.value,
  middlename: middlename.value,
  lastname: lastname.value,
  username: username.value,
  lang: locale.value,
  password: password.value,
  password_confirmation: confirmPassword.value
}));

const register = async () => {
  try {
    await authStore.register(newUser.value, locale.value);
    const localizedNavigate = useLocalizedNavigate();
    await localizedNavigate('/account');
  } catch (e: unknown) {
    if (e instanceof Error) {
      authStore.error = e.message;
    } else {
      authStore.error = t('register.network_error');
    }
  }
};
</script>

<template>
  <div>
    <h2>{{ t('navbar.register').toUpperCase() }}</h2>
    <form @submit.prevent="register">
      <label for="firstname">{{ t('register.firstname') }}</label>
      <input id="firstname" v-model="firstname" type="text" />
      <label for="middlename">{{ t('register.middlename') }}</label>
      <input id="middlename" v-model="middlename" type="text" />
      <label for="lastname">{{ t('register.lastname') }}</label>
      <input id="lastname" v-model="lastname" type="text" />
      <label for="username">{{ t('register.username') }}</label>
      <input id="username" v-model="username" type="text" />
      <label for="email">{{ t('register.email') }}</label>
      <input id="email" v-model="email" type="email" />
      <label for="password">{{ t('register.password') }}</label>
      <input id="password" v-model="password" type="password" />
      <label for="confirmPassword">{{ t('register.confirm_password') }}</label>
      <input id="confirmPassword" v-model="confirmPassword" type="password" />
      <button type="submit">{{ t('register.submit') }}</button>
      <div v-if="authStore.error" style="color: red; margin-top: 1rem">
        {{ authStore.error }}
      </div>
    </form>
  </div>
</template>

<style scoped>
form {
  display: flex;
  flex-direction: column;
  max-width: 400px;
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
