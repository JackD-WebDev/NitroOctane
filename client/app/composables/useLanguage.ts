import { useI18n } from 'vue-i18n';

export function useLanguage() {
  const { setLocale } = useI18n();
  const auth = useAuthStore();

  function setLanguage(lang: SupportedLocale) {
    setLocale(lang);
    if (auth.user) auth.user.preferred_language = lang;
  }

  return {
    setLanguage
  };
}
