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
    '@nuxtjs/i18n',
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
    'nuxt-zod-i18n'
  ],
  image: {},
  eslint: {},
  formkit: {
    autoImport: true
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
  i18n: {
    locales: ['en', 'es'],
    defaultLocale: 'en'
  },
  nitro: {
    externals: {
      inline: ['uuid']
    }
  },
  vite: {
    css: {
      preprocessorOptions: {
        scss: {}
      }
    }
  },
  imports: {
    dirs: ['~/types']
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
