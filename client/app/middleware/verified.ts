export default defineNuxtRouteMiddleware(async () => {
  const authStore = useAuthStore();

  if (!authStore.user) {
    await authStore.fetchUser();
  }

  if (!authStore.user) {
    const localizedNavigate = useLocalizedNavigate();
    return localizedNavigate('/login');
  }

  if (!authStore.isVerified) {
    const verifiedNow = await authStore.refreshVerification?.();

    if (!verifiedNow) {
      const localizedNavigate = useLocalizedNavigate();
      return localizedNavigate('/verify-email');
    }
  }

  return true;
});
