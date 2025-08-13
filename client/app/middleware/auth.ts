export default defineNuxtRouteMiddleware(async () => {
  const authStore = useAuthStore();

  if (!authStore.user) {
    await authStore.fetchUser();
  }

  if (!authStore.user) {
    const localizedNavigate = useLocalizedNavigate();
    return localizedNavigate('/login');
  }

  return true;
});
