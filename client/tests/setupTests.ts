import { beforeEach, beforeAll, afterAll, vi } from 'vitest';
import { createI18n } from 'vue-i18n';
import type { I18n } from 'vue-i18n';
import { createPinia, setActivePinia } from 'pinia';
import { config } from '@vue/test-utils';
import type { Plugin } from '@vue/runtime-core';
import { plugin as FormKit, defaultConfig } from '@formkit/vue';

const __origDebug = console.debug;
const __origError = console.error;
console.debug = ((...args: unknown[]) => {
  try {
    const first = String(args[0] ?? '');
    if (
      first.includes('[zod-i18n] vue-i18n instance missing global.d/global.n')
    ) {
      return undefined;
    }
  } catch {
    // fall through
  }
  return __origDebug.apply(
    console,
    args as unknown as [unknown?, ...unknown[]]
  );
}) as typeof console.debug;

console.error = ((...args: unknown[]) => {
  try {
    const first = String(args[0] ?? '');
    if (
      first.includes('Error matching route rules') ||
      first.includes('Cannot read properties of undefined (reading')
    ) {
      return undefined;
    }
  } catch {
    // fall through
  }
  return __origError.apply(
    console,
    args as unknown as [unknown?, ...unknown[]]
  );
}) as typeof console.error;

interface TestGlobalExtensions {
  __navigateTo?: ReturnType<typeof vi.fn>;
  __localizedNavigate?: ReturnType<typeof vi.fn>;
}

vi.mock('pusher-js', () => ({
  default: class Pusher {
    subscribe() {
      return { bind: () => {} };
    }
    unsubscribe() {
      return;
    }
  }
}));

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

type FetchMock = ((url: string) => Promise<Record<string, unknown>>) & {
  create: () => FetchMock;
};

const baseFetch = vi.fn(async (url: string) => {
  if (typeof url === 'string' && url.includes('/api/check-unique')) {
    return { unique: true };
  }
  return {};
}) as unknown as FetchMock;

baseFetch.create = () => baseFetch;
(globalThis as unknown as { $fetch: FetchMock }).$fetch = baseFetch;

vi.stubGlobal('Pusher', ((..._args: unknown[]) => {
  return {
    subscribe: () => ({ bind: () => {} }),
    unsubscribe: () => {}
  };
}) as unknown);

config.global.stubs = {
  ...(config.global.stubs || {}),
  NuxtLink: { template: '<a><slot /></a>' },
  NuxtImg: true
};

type WarnHandler = (msg: string, vm: unknown, trace?: string) => void;
const setWarnHandler = (h: WarnHandler) => {
  (config as unknown as Record<string, unknown>)['warnHandler'] = h;
};

setWarnHandler((msg: string, _vm: unknown, trace?: string) => {
  if (
    msg.includes('App already provides property with key "Symbol(pinia)"') ||
    msg.includes('has already been registered in target app') ||
    msg.includes('Directive "t" has already been registered')
  ) {
    return;
  }
  console.warn(msg + (trace ?? ''));
});

beforeEach(() => {
  vi.clearAllMocks();
  config.global.plugins = config.global.plugins || [];

  const seen = new Set<unknown>();
  const deduped: unknown[] = [];
  for (const p of config.global.plugins) {
    if (!seen.has(p)) {
      seen.add(p);
      deduped.push(p);
    }
  }
  type Pluggable = Plugin | [Plugin, ...unknown[]];
  config.global.plugins = deduped as unknown as Pluggable[];

  const GH = globalThis as unknown as Record<string, unknown> & {
    __test_pinia?: unknown;
  };
  let piniaInstance = GH.__test_pinia as
    | ReturnType<typeof createPinia>
    | undefined;
  if (!piniaInstance) {
    for (const p of config.global.plugins) {
      if (p && typeof p === 'object') {
        const rp = p as Record<string, unknown>;
        if (rp['_isPinia'] === true) {
          piniaInstance = p as ReturnType<typeof createPinia>;
          break;
        }
      }
    }
  }
  if (!piniaInstance) {
    piniaInstance = createPinia();
    config.global.plugins.push(piniaInstance);
    GH.__test_pinia = piniaInstance;
  }
  setActivePinia(piniaInstance);

  const GH2 = globalThis as unknown as Record<string, unknown> & {
    __formkit_installed?: boolean;
  };
  if (!GH2.__formkit_installed) {
    const hasFormKit = config.global.plugins.some((pl) =>
      Array.isArray(pl) ? pl[0] === FormKit : pl === FormKit
    );
    if (!hasFormKit) {
      config.global.plugins.push([FormKit, defaultConfig()]);
    }
    GH2.__formkit_installed = true;
  }

  const GH3 = globalThis as unknown as Record<string, unknown> & {
    __test_i18n?: unknown;
  };
  let hasI18n = false;
  for (const pl of config.global.plugins) {
    if (!pl || typeof pl !== 'object') continue;
    const rp = pl as Record<string, unknown>;
    if (rp['__isI18n']) {
      hasI18n = true;
      break;
    }
    const g = rp['global'];
    if (g && typeof g === 'object') {
      if ((g as Record<string, unknown>)['__isI18n'] === true) {
        hasI18n = true;
        break;
      }
    }
  }
  if (!hasI18n && !GH3.__test_i18n) {
    const i18n = createI18n({
      legacy: false,
      locale: 'en_US',
      fallbackLocale: 'en_US',
      messages: {
        en_US: {
          navbar: {
            account: 'Account',
            home: 'Home',
            login: 'Log In',
            logout: 'Log Out',
            register: 'Register'
          },
          register: {
            firstname: 'First Name',
            middlename: 'Middle Name',
            lastname: 'Last Name',
            username: 'Username',
            email: 'Email',
            password: 'Password',
            confirm_password: 'Confirm Password',
            submit: 'Register',
            validation_failed: 'Please correct the errors below.',
            network_error: 'Network error occurred. Please try again.',
            validation: {
              firstname_min: 'First name must be at least 2 characters.',
              firstname_max: 'First name cannot exceed 50 characters.',
              firstname_alpha_dash:
                'First name can only contain letters, numbers, dashes, and underscores.',
              middlename_max: 'Middle name cannot exceed 50 characters.',
              middlename_alpha_dash:
                'Middle name can only contain letters, numbers, dashes, and underscores.',
              lastname_min: 'Last name must be at least 2 characters.',
              lastname_max: 'Last name cannot exceed 50 characters.',
              lastname_alpha_dash:
                'Last name can only contain letters, numbers, dashes, and underscores.',
              username_min: 'Username must be at least 3 characters.',
              username_max: 'Username cannot exceed 50 characters.',
              email_min: 'Email must be at least 5 characters.',
              email_max: 'Email cannot exceed 320 characters.',
              email_invalid: 'Please enter a valid email address.',
              password_min: 'Password must be at least 12 characters.',
              password_complexity:
                'Password must contain uppercase, lowercase, numbers, and symbols.',
              passwords_must_match: 'Passwords must match.'
            }
          },
          login: {
            remember_me: 'Remember me',
            forgot_password: 'Forgot Password?',
            validation: {
              email_invalid: 'Please enter a valid email address.',
              password_min: 'Password must be at least 12 characters.'
            }
          }
        },
        es_US: {},
        fr_US: {},
        tl_US: {}
      }
    });
    const i18nObj = i18n as unknown as I18n & Record<string, unknown>;
    i18nObj.__isI18n = true;
    const globalHelpers = {
      d: (d: Date | number | string) => String(d ?? ''),
      n: (n: number | bigint | string) => String(n ?? ''),
      t: (_k: string, _p?: unknown) => '',
      te: (_k: string) => false
    } as const;

    const injected = {
      ...i18nObj,
      global: {
        ...(i18nObj.global as unknown as Record<string, unknown> | undefined),
        ...globalHelpers
      }
    } as unknown as I18n & Record<string, unknown>;
    config.global.plugins.push(injected as unknown as I18n);
    GH3.__test_i18n = injected;
  }

  config.global.stubs = {
    ...(config.global.stubs || {}),
    NuxtLink: { template: '<a><slot /></a>' },
    NuxtImg: true
  };

  const testGlobal = globalThis as typeof globalThis & TestGlobalExtensions;
  if (testGlobal.__navigateTo) {
    testGlobal.__navigateTo.mockClear();
  }
  if (testGlobal.__localizedNavigate) {
    testGlobal.__localizedNavigate.mockClear();
  }
});

let _origWarn: typeof console.warn | undefined;
beforeAll(() => {
  _origWarn = console.warn;
  console.warn = ((...args: unknown[]) => {
    const msg = String(args[0] ?? '');
    if (
      msg.includes('App already provides property with key "Symbol(pinia)"') ||
      msg.includes('has already been registered in target app') ||
      msg.includes('Directive "t" has already been registered')
    ) {
      return undefined;
    }
    return _origWarn?.apply(
      console,
      args as unknown as [unknown?, ...unknown[]]
    );
  }) as typeof console.warn;
});

afterAll(() => {
  if (_origWarn) console.warn = _origWarn;
});

let _origError: typeof console.error | undefined;
beforeAll(() => {
  _origError = console.error;
  console.error = ((...args: unknown[]) => {
    try {
      const first = String(args[0] ?? '');
      if (
        first.includes('Error matching route rules') ||
        first.includes('Cannot read properties of undefined (reading')
      ) {
        return undefined;
      }
    } catch {
      // fall through to default
    }
    return _origError?.apply(
      console,
      args as unknown as [unknown?, ...unknown[]]
    );
  }) as typeof console.error;
});

afterAll(() => {
  if (_origError) console.error = _origError;
});
