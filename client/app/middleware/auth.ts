export default defineNuxtRouteMiddleware(async (_to, _from) => {
  const authStore = useAuthStore();
  const user = authStore.authUser;

  if (!user) {
    return navigateTo('/login');
  }

  return true;
});