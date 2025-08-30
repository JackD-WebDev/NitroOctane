<script setup lang="ts">
const { t } = useI18n();
const health = ref<HealthResponse | null>(null);
const messageStore = useMessageStore();
const { message } = storeToRefs(messageStore);
if (!message.value) messageStore.setStatus('loading');
const statusLetters = computed(() =>
  Array.from(t('status.title').toUpperCase())
);

const fetchHealthStatus = async () => {
  try {
    const data = await useApi<HealthResponse>('/api/health');
    const result = await HealthResponseSchema.safeParseAsync(data);
    if (!result.success) {
      health.value = null;
      messageStore.setStatus('failed');
      return;
    }
    health.value = data;
    messageStore.setMessage(data.message);
    messageStore.setStatus('passed');
  } catch {
    health.value = null;
    messageStore.setStatus('failed');
  }
};

onMounted(() => {
  fetchHealthStatus();
  const id = setInterval(fetchHealthStatus, 60000);
  onBeforeUnmount(() => clearInterval(id));
});
</script>

<template>
  <div class="connection-status">
    <label>
      <ul>
        <li
          v-for="(letter, index) in statusLetters"
          :key="index"
          :style="{ '--index': index + 1 }"
        >
          {{ letter }}
        </li>
      </ul>
    </label>
    <div
      :class="{
        dot: true,
        reddot: !health,
        orangedot: health && messageStore.status !== 'passed',
        greendot: health && messageStore.status === 'passed'
      }"
    />
  </div>
</template>

<style lang="scss" scoped>
.connection-status {
  position: relative;
}

label {
  font-size: 0.8rem;
  display: block;
  text-align: center;
  font-weight: 900;
  position: absolute;
  width: 100%;
  rotate: -104deg;
  left: 0.45rem;
}

label li {
  font-size: 0.75rem;
  position: absolute;
  top: 50%;
  left: calc(50% + 0.3rem);
  transform: translate(-50%, -50%)
    rotate(
      calc(
        180deg / v-bind('statusLetters.length') * (var(--index) - 0.5) + 15deg
      )
    )
    translateY(calc(var(--radius, 3) * -1ch));
}

.dot {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 50%;
  position: relative;
  margin: 0 auto;
  &.reddot {
    background-color: red;
  }
  &.orangedot {
    background-color: orange;
  }
  &.greendot {
    background-color: var(--primary-color);
    box-shadow: 0 0 1rem var(--primary-color), 0 0 2rem var(--primary-color),
      0 0 3rem var(--primary-color);
    animation: glow 1.5s infinite alternate;
  }
}

@keyframes glow {
  from {
    box-shadow: 0 0 0.5rem var(--primary-color), 0 0 1rem var(--primary-color),
      0 0 1.5rem var(--primary-color);
  }
  to {
    box-shadow: 0 0 1.5rem var(--primary-color), 0 0 2rem var(--primary-color),
      0 0 2.5rem var(--primary-color);
  }
}
</style>
