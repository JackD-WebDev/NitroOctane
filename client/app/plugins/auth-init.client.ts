export default defineNuxtPlugin(async () => {
  if (import.meta.client && import.meta.env.MODE !== 'test') {
    const authStore = useAuthStore();
    try {
      await authStore.fetchUser(false);
    } catch {
      console.debug('[auth-init] No valid session found on startup');
    }
  }
});
