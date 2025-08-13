<script setup lang="ts">
const { t } = useI18n();
definePageMeta({
  middleware: ['auth'],
  title: 'Account'
});
useAppTitle(t('navbar.account'));

const authStore = useAuthStore();
</script>

<template>
  <div>
    <h2>{{ t('navbar.account').toUpperCase() }}</h2>
    <div v-if="authStore.user">
      <p>
        <strong>{{ t('register.username') }}:</strong>
        {{ authStore.user.username }}
      </p>
      <p>
        <strong>{{ t('register.email') }}:</strong> {{ authStore.user.email }}
      </p>
    </div>
    <div v-else>
      <p>{{ t('account.loading') }}</p>
    </div>
  </div>
</template>
