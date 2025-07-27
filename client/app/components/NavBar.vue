<script setup lang="ts">
const authStore = useAuthStore();
const { isLoggedIn } = storeToRefs(authStore);

const NAVBAR_LINKS = [
  { name: 'Home', path: '' },
  {
    name: 'Log In',
    path: 'login',
    showWhenLoggedIn: false
  },
  {
    name: 'Register',
    path: 'register',
    showWhenLoggedIn: false,
    showWhenLoggedOut: true
  },
  {
    name: 'Account',
    path: 'account',
    showWhenLoggedIn: true,
    showWhenLoggedOut: false
  },
  {
    name: 'Log Out',
    onClick: () => {
      authStore.logOut();
      navigateTo('/');
    },
    path: '',
    showWhenLoggedIn: true,
    showWhenLoggedOut: false
  }
].map((link, index) => ({ ...link, id: index + 1, path: `/${link.path}` }));

const filteredLinks = computed(() => {
  return NAVBAR_LINKS.filter(
    (link) =>
      (link.showWhenLoggedIn === undefined ||
        link.showWhenLoggedIn === isLoggedIn.value) &&
      (link.showWhenLoggedOut === undefined ||
        link.showWhenLoggedOut !== isLoggedIn.value)
  );
});
</script>
<template>
  <nav>
    <ul>
      <li>
        <LazyStatusMessage />
      </li>
      <li>
        <LazyConnectionStatus />
      </li>
      <li v-for="page in filteredLinks" :key="page.id">
        <button v-if="page.onClick" class="nav-button" @click="page.onClick">
          {{ page.name.toUpperCase() }}
        </button>
        <NuxtLink v-else :to="page.path">{{
          page.name.toUpperCase()
        }}</NuxtLink>
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.nav-button {
  background: none;
  border: none;
  color: inherit;
  font: inherit;
  cursor: pointer;
  text-decoration: none;
  padding: 0;
}

.nav-button:hover {
  text-decoration: underline;
}
</style>
