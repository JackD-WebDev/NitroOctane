<script setup lang="ts">
const { t } = useI18n();

definePageMeta({
  title: 'Home'
});
useAppTitle(t('navbar.home'));
</script>

<template>
  <div>
    <h2>{{ t('navbar.home').toUpperCase() }}</h2>
  </div>
</template>
