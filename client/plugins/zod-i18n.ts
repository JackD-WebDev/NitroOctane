import { z } from 'zod';
import { defineNuxtPlugin } from 'nuxt/app';
import type { NuxtApp } from 'nuxt/app';

export default defineNuxtPlugin(async (nuxtApp: NuxtApp) => {
  const i18n =
    (nuxtApp as unknown as { $i18n?: unknown }).$i18n ||
    nuxtApp.vueApp.config.globalProperties.$i18n;
  if (!i18n) return;
  // Ensure the i18n instance has the expected Composition API helpers before
  // passing it to zod-vue-i18n. In some SSR/runtime setups the i18n object may
  // not expose `global.d` / `global.n` yet which would cause a hard crash.
  const hasFormatters = !!(
    (i18n as any)?.global &&
    typeof (i18n as any).global.d === 'function' &&
    typeof (i18n as any).global.n === 'function'
  );
  if (!hasFormatters) {
    // i18n is present but not ready for zod-vue-i18n. Skip configuring Zod
    // error localization to avoid runtime exceptions in environments where
    // vue-i18n hasn't fully initialized yet (SSR hooks, early plugin runs).
    // This is safe: Zod will fall back to its default error messages.
    // Leave a small debug trace for developers.
    // eslint-disable-next-line no-console
    console.debug(
      '[zod-i18n] vue-i18n instance missing global.d/global.n, skipping Zod i18n setup'
    );
  } else {
    // Prefer the in-repo helper when present to avoid relying on the external
    // package that may have packaging issues. The local helper implements the
    // same makeZodI18nMap contract.
    let makeZodI18nMap: unknown = undefined;
    try {
      // Try local helper first (fast, ESM import)
      // eslint-disable-next-line import/no-unresolved, @typescript-eslint/no-var-requires
      // Note: Using dynamic import so this code runs safely in SSR and test envs.
      // The local module path is relative to the client root.
      // @ts-ignore - runtime-only import
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      makeZodI18nMap = (await import('../shared/zod-i18n-v4')).makeZodI18nMap;
    } catch (errLocal) {
      try {
        const zodI18nPath = ['zod-vue-i18n', 'v4'].join('/');
        const mod = await import(/* @vite-ignore */ zodI18nPath);
        makeZodI18nMap = (mod as any)?.makeZodI18nMap;
      } catch (errPkg) {
        // eslint-disable-next-line no-console
        console.debug(
          '[zod-i18n] failed to import zod-vue-i18n (local and package), skipping Zod localization',
          errLocal,
          errPkg
        );
      }
    }

    if (typeof makeZodI18nMap !== 'function') {
      // eslint-disable-next-line no-console
      console.debug('[zod-i18n] makeZodI18nMap not found, skipping');
    } else {
      try {
        const localeErrorMapper = (makeZodI18nMap as any)(i18n);
        if (typeof localeErrorMapper === 'function') {
          (
            z as unknown as {
              config?: (args: { localeError: unknown }) => void;
            }
          ).config?.({
            localeError: localeErrorMapper
          });
        } else {
          // eslint-disable-next-line no-console
          console.debug(
            '[zod-i18n] makeZodI18nMap did not return a mapper function, skipping'
          );
        }
      } catch (err) {
        // If calling the helper throws, don't crash the app in SSR/runtime.
        // eslint-disable-next-line no-console
        console.debug(
          '[zod-i18n] error while creating localeError mapper',
          err
        );
      }
    }
  }

  const importJson = async (path: string) =>
    import(/* @vite-ignore */ path)
      .then((m) => (m as unknown as { default?: unknown }).default ?? m)
      .catch(() => undefined);

  const baseByLocale: Record<string, string> = {
    en_US: 'en',
    es_US: 'es',
    fr_US: 'fr',
    tl_US: 'tl'
  };

  for (const [nuxtLocale, base] of Object.entries(baseByLocale)) {
    const path = ['zod-vue-i18n', 'locales', 'v4', `${base}.json`].join('/');
    const msgs = (await importJson(path)) as
      | Record<string, unknown>
      | undefined;
    if (msgs) {
      // @ts-expect-error - vue-i18n instance from @nuxtjs/i18n
      i18n.global?.mergeLocaleMessage?.(nuxtLocale, msgs);
    }
  }
});
