import { defineVitestConfig } from '@nuxt/test-utils/config';

export default defineVitestConfig({
  test: {
    environment: 'nuxt',
    include: ['**/*.test.ts'],
    coverage: {
      provider: 'istanbul',
    },
  },
});
