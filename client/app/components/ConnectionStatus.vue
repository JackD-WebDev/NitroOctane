<script setup lang="ts">
const health = ref<HealthResponse | null>(null);
const messageStore = useMessageStore();
const { message } = storeToRefs(messageStore);
if (!message.value) {
  messageStore.setMessage('LOADING...');
}

const fetchHealthStatus = async () => {
  try {
    const data = await useApi<HealthResponse>('/api/health');
    const result = HealthResponseSchema.safeParse(data);
    if (!result.success) {
      health.value = null;
      messageStore.setMessage('FAILED TO RETRIEVE HEALTH STATUS');
      return;
    }
    health.value = data;
    messageStore.setMessage(data.message);
  } catch (error) {
    console.error('Health check failed:', error);
    health.value = null;
    messageStore.setMessage('FAILED TO RETRIEVE HEALTH STATUS');
  }
};

onMounted(() => {
  fetchHealthStatus();
  setInterval(fetchHealthStatus, 60000);
});
</script>

<template>
  <div>
    <label>
      <span :style="{ '--index': 1 }">S</span>
      <span :style="{ '--index': 2 }">T</span>
      <span :style="{ '--index': 3 }">A</span>
      <span :style="{ '--index': 4 }">T</span>
      <span :style="{ '--index': 5 }">U</span>
      <span :style="{ '--index': 6 }">S</span>
    </label>
    <div
      :class="{
        dot: true,
        reddot: !health,
        orangedot: health && message !== 'HEALTH CHECK PASSED.',
        greendot: health && message === 'HEALTH CHECK PASSED.'
      }"
    />
  </div>
</template>

<style lang="scss" scoped>
label {
  font-size: 0.8rem;
  display: block;
  text-align: center;
  font-weight: 900;
  position: relative;
  width: 100%;
  rotate: -104deg;
}

label span {
  font-size: 0.75rem;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(calc(180deg / 6 * var(--index)))
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
