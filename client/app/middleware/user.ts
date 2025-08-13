export default defineNuxtRouteMiddleware(async () => {
  const authStore = useAuthStore();

  if (
    (!authStore.user || (authStore.isAuthenticated && !authStore.user)) &&
    import.meta.client
  ) {
    try {
      await authStore.fetchUser();
    } catch {
      authStore.isAuthenticated = false;
      authStore.setUser(null);
    }
  }
});
