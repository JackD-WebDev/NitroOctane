<script setup lang="ts">
const { t } = useI18n();
const { fullAppTitle } = useRuntimeConfig().public;
const { applicationName } = useRuntimeConfig().public;

const copyright = `${new Date().getFullYear()} ${applicationName}. ${t(
  'layout.copyright'
).toUpperCase()}`;
</script>

<template>
  <div>
    <NavBar />
    <div class="layout-main">
      <aside class="layout-sidebar">
        <h1>{{ t('layout.title').toUpperCase() }}</h1>
      </aside>
      <main class="layout-content">
        <slot />
      </main>
    </div>
    <footer>
      <p v-if="fullAppTitle">{{ fullAppTitle }}</p>
      <p v-if="copyright">&copy; {{ copyright }}</p>
    </footer>
  </div>
</template>

<style scoped lang="scss">
.layout-main {
  display: flex;
  flex-direction: row;
}
.layout-sidebar {
  width: clamp(2rem, 100%, 5rem);
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  padding: 2rem 1.5rem 1rem 1.5rem;
  height: 100%;
  height: auto;
  border-right: #2c3e50 1px solid;
}
.layout-sidebar h1 {
  writing-mode: vertical-lr;
  transform: rotate(180deg);
  font-size: 1.2rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  height: auto;
  color: var(--color-primary, #2c3e50);
  margin: 0;
}
.layout-content {
  flex: 1;
  padding: 2rem 2rem 3rem 2rem;
  overflow-y: auto;
  min-width: 0;
}
footer {
  text-align: center;
  padding: 1rem;
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100vw;
  font-size: 0.875em;
  background: transparent;
  z-index: 10;
}
footer p {
  margin: 0;
  color: #6c757d;
}
</style>
