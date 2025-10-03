import { defineVitestConfig } from '@nuxt/test-utils/config';

export default defineVitestConfig({
  test: {
    environment: 'nuxt',
    root: '.',
    include: ['tests/**/*.test.ts'],
    setupFiles: ['tests/setupTests.ts'],
    coverage: {
      provider: 'istanbul',
    },
  },
});
