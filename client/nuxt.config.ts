export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['@/assets/styles/main.scss'],
  modules: [
    '@pinia/nuxt',
    'nuxt-laravel-echo',
    '@pinia-plugin-persistedstate/nuxt',
    '@nuxt/eslint',
    '@nuxt/image',
    '@nuxt/test-utils/module',
    '@nuxt/scripts',
    '@formkit/nuxt',
    '@nuxtjs/i18n',
    '@vite-pwa/nuxt',
    '@vueuse/nuxt',
    'magic-regexp/nuxt',
    // 'nuxt-zod-i18n', // Temporarily disabled - incompatible with Zod v4
    '@nuxt/scripts'
  ],
  image: {},
  eslint: {},
  pwa: {
    manifest: {
      name: 'NitroOctane',
      short_name: 'NitroOctane',
      description: 'NitroOctane',
      theme_color: '#5f0',
      lang: 'en'
    }
  },
  formkit: {
    autoImport: true,
    configFile: '~/formkit.config.mts'
  },
  i18n: {
    vueI18n: './i18n.config.mts',
    strategy: 'prefix_except_default',
    defaultLocale: 'en_US',
    langDir: 'locales',
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      redirectOn: 'root',
      alwaysRedirect: false
    },
    locales: [
      { code: 'en_US', iso: 'en-US', name: 'English (US)', file: 'en_US.json' },
      { code: 'es_US', iso: 'es-US', name: 'Español (US)', file: 'es_US.json' },
      {
        code: 'fr_US',
        iso: 'fr-US',
        name: 'Français (US)',
        file: 'fr_US.json'
      },
      { code: 'tl_US', iso: 'tl-US', name: 'Tagalog (US)', file: 'tl_US.json' }
    ]
  },
  echo: {
    broadcaster: 'reverb',
    key: process.env.NUXT_PUBLIC_REVERB_APP_KEY,
    port: 80,
    scheme: 'http',
    authentication: {
      baseUrl: 'http://localhost:8000/api',
      mode: 'cookie'
    }
  },
  nitro: {
    externals: {
      inline: ['uuid']
    }
  },
  vite: {
    optimizeDeps: {
      include: ['pusher-js']
    },
    css: {
      preprocessorOptions: {
        scss: {}
      }
    }
  },
  imports: {
    dirs: ['~/shared/types']
  },
  experimental: {
    cookieStore: true
  },
  runtimeConfig: {
    appName: process.env.APP_NAME || 'NitroOctane',
    appVersion: process.env.APP_VERSION || '0.0.0',
    releaseDate: process.env.RELEASE_DATE || '',
    releaseStatus: process.env.RELEASE_STATUS || 'development',
    releaseAlias: process.env.RELEASE_ALIAS || '',
    apiUrl: process.env.NUXT_API_URL || 'http://localhost/api/',
    public: {
      applicationName:
        process.env.NUXT_PUBLIC_APPLICATION_NAME ||
        process.env.APP_NAME ||
        'NitroOctane',
      appVersion: process.env.APP_VERSION || '0.0.0',
      fullAppTitle: process.env.NUXT_PUBLIC_FULL_APP_NAME || 'NitroOctane',
      clientUrl: process.env.NUXT_CLIENT_URL || 'http://localhost',
      appDomain: process.env.NUXT_PUBLIC_DOMAIN || 'localhost',
      reverbKey:
        process.env.NUXT_PUBLIC_REVERB_APP_KEY ||
        process.env.NUXT_PUBLIC_REVERB_KEY ||
        '',
      reverbHost: process.env.NUXT_PUBLIC_REVERB_HOST || 'localhost',
      reverbAuthEndpoint:
        process.env.NUXT_PUBLIC_REVERB_AUTH_ENDPOINT || '/broadcasting/auth',
      reverbPort: process.env.NUXT_PUBLIC_REVERB_PORT
        ? Number(process.env.NUXT_PUBLIC_REVERB_PORT)
        : 80,
      reverbScheme: process.env.NUXT_PUBLIC_REVERB_SCHEME || 'http'
    }
  }
});
