export default defineNuxtPlugin(async () => {
  if (import.meta.client) {
    const authStore = useAuthStore();
    try {
      await authStore.fetchUser(false);
    } catch {
      console.debug('[auth-init] No valid session found on startup');
    }
  }
});
