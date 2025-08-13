export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore();
  const isGuestPage =
    to?.path?.endsWith('/login') || to?.path?.endsWith('/register');
  if (isGuestPage && (!authStore.user || !authStore.isAuthenticated)) {
    authStore.error = '';
  }

  if (authStore.user && authStore.isAuthenticated) {
    const localizedNavigate = useLocalizedNavigate();
    return localizedNavigate('/');
  }
});
