export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore();
  const path = to?.path || '';
  const isGuestPage =
    path.endsWith('/login') ||
    path.endsWith('/register') ||
    path.endsWith('/forgot-password') ||
    path.endsWith('/reset-password');

  if (isGuestPage && (!authStore.user || !authStore.isAuthenticated)) {
    authStore.error = '';
  }

  if (authStore.user && authStore.isAuthenticated && isGuestPage) {
    const localizedNavigate = useLocalizedNavigate();
    return localizedNavigate('/');
  }
});
