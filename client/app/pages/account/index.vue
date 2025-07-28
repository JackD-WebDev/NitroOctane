<script setup lang="ts">
const pageTitle = 'ACCOUNT';
definePageMeta({
  middleware: ['auth'],
  title: pageTitle
});
useAppTitle(pageTitle);

const authStore = useAuthStore();
const router = useRouter();

onMounted(() => {
  if (!authStore.isLoggedIn || !authStore.getAuth) {
    router.replace('/login');
  }
});
</script>

<template>
  <div>
    <h2>ACCOUNT</h2>
    <div v-if="authStore.getAuth">
      <p><strong>Username:</strong> {{ authStore.getAuth.username }}</p>
      <p><strong>Email:</strong> {{ authStore.getAuth.email }}</p>
    </div>
    <div v-else>
      <p>Loading user info...</p>
    </div>
  </div>
</template>
