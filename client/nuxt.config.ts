export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },
  css: ['@/assets/styles/main.scss'],
  future: {
    compatibilityVersion: 4
  },
  modules: [
    '@pinia/nuxt',
    '@pinia-plugin-persistedstate/nuxt',
    '@vueuse/nuxt',
    '@nuxt/test-utils/module',
    '@nuxt/image',
    '@nuxt/eslint',
    'magic-regexp/nuxt',
    '@vite-pwa/nuxt',
    'nuxt-lodash',
    '@formkit/nuxt',
    'nuxt-purgecss',
    '@nuxt/scripts',
    '@nuxtjs/i18n',
    'nuxt-laravel-echo'
  ],
  image: {},
  eslint: {},
  formkit: {
    autoImport: true,
    configFile: '~/formkit.config.mts'
  },
  i18n: {
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
    ],
    vueI18n: './i18n.config.ts'
  },
  pwa: {
    manifest: {
      name: 'NitroOctane',
      short_name: 'NitroOctane',
      description: 'NitroOctane',
      theme_color: '#5f0',
      lang: 'en'
    }
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
    dirs: ['~/types', '~/shared/types']
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
      appDomain: process.env.NUXT_PUBLIC_DOMAIN || 'localhost'
    }
  }
});
