<script setup lang="ts">
const authStore = useAuthStore();
const router = useRouter();
const email = ref('');
const password = ref('');

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
    const user = await authStore.logIn(credentials.value);
    if (user) {
      clearForm();
      await router.push('/account');
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
    <form @submit.prevent="login">
      <div v-if="authStore.error" class="error">
        {{ authStore.error }}
      </div>
      <label for="email">EMAIL</label>
      <input id="email" v-model="email" type="email" placeholder="Email" />
      <label for="password">PASSWORD</label>
      <input
        id="password"
        v-model="password"
        type="password"
        placeholder="Password"
      />
      <button type="submit">Log In</button>
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
