<script setup lang="ts">
const firstname = ref('');
const middlename = ref('');
const lastname = ref('');
const email = ref('');
const username = ref('');
const password = ref('');
const confirmPassword = ref('');
const authStore = useAuthStore();
const router = useRouter();

const newUser = computed(() => ({
  email: email.value,
  firstname: firstname.value,
  middlename: middlename.value,
  lastname: lastname.value,
  username: username.value,
  password: password.value,
  password_confirmation: confirmPassword.value
}));

const register = async () => {
  try {
    await authStore.register(newUser.value);
    router.push('/');
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
    <h2>REGISTER</h2>
    <form @submit.prevent="register">
      <label for="firstname">FIRST NAME</label>
      <input id="firstname" v-model="firstname" type="text" />
      <label for="middlename">MIDDLE NAME</label>
      <input id="middlename" v-model="middlename" type="text" />
      <label for="lastname">LAST NAME</label>
      <input id="lastname" v-model="lastname" type="text" />
      <label for="username">USERNAME</label>
      <input id="username" v-model="username" type="text" />
      <label for="email">EMAIL</label>
      <input id="email" v-model="email" type="email" />
      <label for="password">PASSWORD</label>
      <input id="password" v-model="password" type="password" />
      <label for="confirmPassword">CONFIRM PASSWORD</label>
      <input id="confirmPassword" v-model="confirmPassword" type="password" />
      <button type="submit">REGISTER</button>
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
