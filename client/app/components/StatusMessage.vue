<script setup lang="ts">
const { t } = useI18n();
const messageStore = useMessageStore();
const { message } = storeToRefs(messageStore);
</script>

<template>
  <p>{{ t('status.title').toUpperCase() }}: {{ message }}</p>
</template>

<style lang="scss" scoped>
p {
  font-size: 1rem;
}
</style>
