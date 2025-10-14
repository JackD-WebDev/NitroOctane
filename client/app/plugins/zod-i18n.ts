import { z } from 'zod';
import { defineNuxtPlugin } from 'nuxt/app';

type MinimalI18n = {
  global?: {
    d?: (d: Date | number | string) => string;
    n?: (n: number | bigint | string) => string;
    t?: (...args: unknown[]) => string;
    te?: (key: string) => boolean;
    mergeLocaleMessage?: (
      locale: string,
      msgs: Record<string, unknown>
    ) => void;
  };
} & Record<string, unknown>;

export default defineNuxtPlugin(async (nuxtApp) => {
  const rawI18n =
    (nuxtApp as unknown as { $i18n?: unknown })?.$i18n ??
    nuxtApp.vueApp.config.globalProperties.$i18n;
  const i18n = rawI18n as MinimalI18n | undefined;
  if (!i18n) return;
  const hasFormatters = !!(
    i18n.global &&
    typeof i18n.global.d === 'function' &&
    typeof i18n.global.n === 'function'
  );
  if (!hasFormatters) {
    console.debug(
      '[zod-i18n] vue-i18n instance missing global.d/global.n, skipping Zod i18n setup'
    );
  } else {
    let makeZodI18nMap: unknown = undefined;
    try {
      makeZodI18nMap = (await import('../../shared/zod-i18n-v4'))
        .makeZodI18nMap;
    } catch (errLocal) {
      try {
        const zodI18nPath = ['zod-vue-i18n', 'v4'].join('/');
        const mod = await import(/* @vite-ignore */ zodI18nPath);
        makeZodI18nMap = (mod as unknown as { makeZodI18nMap?: unknown })
          ?.makeZodI18nMap;
      } catch (errPkg) {
        console.debug(
          '[zod-i18n] failed to import zod-vue-i18n (local and package), skipping Zod localization',
          errLocal,
          errPkg
        );
      }
    }

    if (typeof makeZodI18nMap !== 'function') {
      console.debug('[zod-i18n] makeZodI18nMap not found, skipping');
    } else {
      try {
        const localeErrorMapper = (
          makeZodI18nMap as unknown as (i: MinimalI18n) => unknown
        )(i18n as MinimalI18n);
        if (typeof localeErrorMapper === 'function') {
          (
            z as unknown as {
              config?: (args: { localeError: unknown }) => void;
            }
          ).config?.({
            localeError: localeErrorMapper
          });
        } else {
          console.debug(
            '[zod-i18n] makeZodI18nMap did not return a mapper function, skipping'
          );
        }
      } catch (err) {
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
      const g = i18n.global as
        | {
            mergeLocaleMessage?: (
              locale: string,
              msgs: Record<string, unknown>
            ) => void;
          }
        | undefined;
      g?.mergeLocaleMessage?.(nuxtLocale, msgs);
    }
  }
});
