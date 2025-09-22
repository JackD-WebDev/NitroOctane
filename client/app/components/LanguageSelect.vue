<script setup lang="ts">
const { locale, locales } = useI18n();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const { setLanguage } = useLanguage();

const selectedLang = ref<SupportedLocale>(
  (locale.value as SupportedLocale) || 'en_US'
);

const isValidLocale = (code: string): code is SupportedLocale => {
  return ['en_US', 'es_US', 'fr_US', 'tl_US'].includes(code as SupportedLocale);
};

const supportedLangs = computed<SupportedLocale[]>(() => {
  const codes = locales.value.map((l) => l.code);
  return codes.filter((c): c is SupportedLocale => isValidLocale(c));
});

const getLanguageDisplayName = (langCode: string): string => {
  const localeConfig = locales.value.find((l) => l.code === langCode);

  if (localeConfig) {
    return (localeConfig as { name?: string; code: string }).name || langCode;
  }

  return langCode;
};

onMounted(() => {
  if (import.meta.server) return;

  if (
    user.value?.preferred_language &&
    isValidLocale(user.value.preferred_language) &&
    supportedLangs.value.includes(user.value.preferred_language)
  ) {
    selectedLang.value = user.value.preferred_language;
    setLanguage(user.value.preferred_language);
    return;
  }

  const storedLang = import.meta.client
    ? localStorage.getItem('user-preferred-language')
    : null;
  if (
    storedLang &&
    isValidLocale(storedLang) &&
    supportedLangs.value.includes(storedLang)
  ) {
    selectedLang.value = storedLang;
    setLanguage(storedLang);
    return;
  }

  const current = locale.value as string;
  if (isValidLocale(current) && supportedLangs.value.includes(current)) {
    selectedLang.value = current;
  }
});

watch(user, (newUser) => {
  if (
    newUser?.preferred_language &&
    isValidLocale(newUser.preferred_language) &&
    supportedLangs.value.includes(newUser.preferred_language)
  ) {
    selectedLang.value = newUser.preferred_language;
  }
});

watch(selectedLang, async (newLang) => {
  if (supportedLangs.value.includes(newLang)) {
    if (import.meta.client) {
      localStorage.setItem('user-preferred-language', newLang);
    }

    setLanguage(newLang);

    const switchLocalePath = useSwitchLocalePath();
    const newPath = switchLocalePath(newLang);
    if (newPath && newPath !== useRoute().path) {
      const localizedNavigate = useLocalizedNavigate();
      await localizedNavigate(newPath);
    }
  }
});
</script>

<template>
  <ClientOnly>
    <div class="language-select">
      <select v-model="selectedLang" class="language-dropdown">
        <option v-for="lang in supportedLangs" :key="lang" :value="lang">
          {{ getLanguageDisplayName(lang) }}
        </option>
      </select>
    </div>
  </ClientOnly>
</template>

<style lang="scss" scoped>
.language-select {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  margin: 1rem 0;
}

.language-dropdown {
  background-color: var(--button-bg);
  color: var(--primary-color);
  border: 5px solid var(--form-border);
  border-radius: 0.4rem;
  padding: 12px 24px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1.6rem;
  text-transform: uppercase;
  transition: background-color 0.2s, border-color 0.2s, color 0.2s;
  font-family: inherit;

  &:hover {
    background-color: var(--form-hover-bg);
    border-color: var(--form-border);
    color: var(--button-text);
  }

  &:focus {
    outline: 2px solid var(--form-border);
    background-color: var(--form-hover-bg);
    box-shadow: none;
  }
}
</style>
