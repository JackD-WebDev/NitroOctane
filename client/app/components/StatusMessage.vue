<script setup lang="ts">
const messageStore = useMessageStore();
const { message } = storeToRefs(messageStore);
</script>

<template>
  <p>STATUS: {{ message }}</p>
</template>

<style lang="scss" scoped>
p {
  font-size: 1rem;
}
</style>
