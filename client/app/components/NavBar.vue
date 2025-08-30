<script setup lang="ts">
const authStore = useAuthStore();
const { isAuthenticated } = storeToRefs(authStore);
const { handleLogout } = useLogout();
const localePath = useLocalePath();
const { t, locale } = useI18n();

type NavLink = {
  nameKey: string;
  path?: string;
  showWhenLoggedIn?: boolean;
  showWhenLoggedOut?: boolean;
  onClick?: () => void | Promise<void>;
};

const BASE_LINKS = computed<NavLink[]>(() => [
  {
    nameKey: 'navbar.login',
    path: 'login',
    showWhenLoggedIn: false
  },
  {
    nameKey: 'navbar.register',
    path: 'register',
    showWhenLoggedIn: false,
    showWhenLoggedOut: true
  },
  {
    nameKey: 'navbar.account',
    path: 'account',
    showWhenLoggedIn: true,
    showWhenLoggedOut: false
  },
  {
    nameKey: 'navbar.logout',
    onClick: handleLogout,
    path: '',
    showWhenLoggedIn: true,
    showWhenLoggedOut: false
  }
]);

const NAVBAR_LINKS = computed<Array<NavLink & { id: number; path: string }>>(
  () => {
    void locale.value;
    return BASE_LINKS.value.map((link, index) => ({
      ...link,
      id: index + 1,
      path: link.path ? localePath(`/${link.path}`) : localePath('/')
    }));
  }
);

const filteredLinks = computed(() => {
  return NAVBAR_LINKS.value.filter(
    (link) =>
      (link.showWhenLoggedIn === undefined ||
        link.showWhenLoggedIn === isAuthenticated.value) &&
      (link.showWhenLoggedOut === undefined ||
        link.showWhenLoggedOut !== isAuthenticated.value)
  );
});
</script>

<template>
  <nav>
    <ul>
      <li>
        <NuxtLink :to="localePath('/')">
          <LazyNitroOctaneLogo />
        </NuxtLink>
      </li>
      <li>
        <LazyStatusMessage />
      </li>
      <li>
        <LazyConnectionStatus />
      </li>
      <li>
        <LazyLanguageSelect />
      </li>
      <ClientOnly>
        <li v-for="page in filteredLinks" :key="page.id">
          <button v-if="page.onClick" class="btn-nav" @click="page.onClick">
            {{ t(page.nameKey).toUpperCase() }}
          </button>
          <NuxtLink v-else :to="page.path" class="btn-nav">{{
            t(page.nameKey).toUpperCase()
          }}</NuxtLink>
        </li>
      </ClientOnly>
    </ul>
  </nav>
</template>

<style scoped>
nav ul {
  display: flex;
  align-items: center;
  list-style: none;
  padding: 0;
  margin: 0;
}

nav ul > li:first-child {
  margin-right: auto;
}
</style>
