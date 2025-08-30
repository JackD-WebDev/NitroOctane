export default defineNuxtRouteMiddleware((to, from) => {
  const i18n = useI18n();
  const auth = useAuthStore();
  const cookieLocale = useCookie('i18n_redirected');

  let newLocale: string | string[] | undefined =
    to.params.locale ||
    cookieLocale.value ||
    auth.user?.preferred_language ||
    i18n.locale.value;
  if (typeof newLocale === 'undefined') newLocale = 'en_US';
  if (Array.isArray(newLocale)) newLocale = newLocale[0] ?? 'en_US';
  if (typeof newLocale !== 'string') newLocale = 'en_US';

  const parseResult = SupportedLocaleSchema.safeParse(newLocale);
  if (!parseResult.success) {
    newLocale = 'en_US';
  }

  if (i18n.locale.value !== newLocale) {
    console.debug(
      `[locale middleware] Changing locale from '${
        i18n.locale.value
      }' to '${newLocale}' (from: ${
        from && from.fullPath ? from.fullPath : 'unknown'
      })`
    );
    i18n.setLocale(newLocale as SupportedLocale);
  }
});
