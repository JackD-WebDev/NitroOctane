<script setup lang="ts">
const { fullAppTitle } = useRuntimeConfig().public;
const { applicationName } = useRuntimeConfig().public;

const copyright = `${new Date().getFullYear()} ${applicationName}. All rights reserved.`;
</script>

<template>
  <div>
    <NavBar />
    <h1>LAYOUT</h1>

    <slot />
    <footer>
      <p v-if="fullAppTitle">{{ fullAppTitle }}</p>
      <p v-if="copyright">&copy; {{ copyright }}</p>
    </footer>
  </div>
</template>

<style scoped>
footer {
  text-align: center;
  padding: 1rem;
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  font-size: 0.875em;
}
footer p {
  margin: 0;
  color: #6c757d;
}
</style>
