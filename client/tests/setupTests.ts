import { beforeAll, beforeEach, vi } from 'vitest';
import { createI18n } from 'vue-i18n';
import { createPinia, setActivePinia } from 'pinia';
import { config } from '@vue/test-utils';
import { plugin as FormKit, defaultConfig } from '@formkit/vue';

interface TestGlobalExtensions {
  __navigateTo?: ReturnType<typeof vi.fn>;
  __localizedNavigate?: ReturnType<typeof vi.fn>;
}

beforeAll(() => {
  const pinia = createPinia();
  setActivePinia(pinia);
  config.global.plugins = config.global.plugins || [];
  config.global.plugins.push(pinia);

  config.global.plugins.push([FormKit, defaultConfig()]);

  vi.stubGlobal('useRoute', () => ({ query: {} }));
  vi.stubGlobal('useRouter', () => ({ push: vi.fn() }));
  vi.stubGlobal('useAppTitle', () => vi.fn());

  const mockNavigateTo = vi.fn(async (path: string) => path);
  vi.stubGlobal('navigateTo', mockNavigateTo);
  vi.stubGlobal('useLocalePath', () => (path: string) => path);

  const mockLocalizedNavigate = vi.fn(async (path: string) => {
    return mockNavigateTo(path);
  });
  vi.stubGlobal('useLocalizedNavigate', () => mockLocalizedNavigate);

  (globalThis as typeof globalThis & TestGlobalExtensions).__navigateTo =
    mockNavigateTo;
  (globalThis as typeof globalThis & TestGlobalExtensions).__localizedNavigate =
    mockLocalizedNavigate;

  (globalThis as unknown as { $fetch: ReturnType<typeof vi.fn> }).$fetch =
    vi.fn(async (url: string) => {
      if (typeof url === 'string' && url.includes('/api/check-unique')) {
        return { unique: true };
      }
      return {};
    });

  const i18n = createI18n({
    legacy: false,
    locale: 'en_US',
    fallbackLocale: 'en_US',
    messages: { en_US: {}, es_US: {}, fr_US: {}, tl_US: {} }
  });
  config.global.plugins.push(i18n);

  config.global.stubs = {
    ...(config.global.stubs || {}),
    NuxtLink: { template: '<a><slot /></a>' },
    NuxtImg: true
  };
});

beforeEach(() => {
  vi.clearAllMocks();
  const testGlobal = globalThis as typeof globalThis & TestGlobalExtensions;
  if (testGlobal.__navigateTo) {
    testGlobal.__navigateTo.mockClear();
  }
  if (testGlobal.__localizedNavigate) {
    testGlobal.__localizedNavigate.mockClear();
  }
});
