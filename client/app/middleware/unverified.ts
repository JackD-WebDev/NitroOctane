export default defineNuxtRouteMiddleware(async () => {
  const authStore = useAuthStore();
  const allowUnverified = useState<boolean>('allowUnverified', () => false);
  allowUnverified.value = true;

  if (!authStore.user && import.meta.client) {
    try {
      await authStore.fetchUser();
    } catch {
      authStore.isAuthenticated = false;
      authStore.setUser(null);
    }
  }

  return true;
});
