export default defineNuxtRouteMiddleware((_to, _from) => {
  const token = useCookie('token');
  if (token.value) {
    return navigateTo('/');
  }
});
