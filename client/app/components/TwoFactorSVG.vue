<template>
  <div class="two-factor-svg">
    <img v-if="imgSrc" :src="imgSrc" alt="Two-factor QR" />
    <slot v-else />
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{ svgContent?: string | null }>(), {
  svgContent: null
});

const imgSrc = ref<string | null>(null);

const toDataUrl = (svg: string) => {
  if (svg.startsWith('data:')) return svg;
  if (svg.trim().startsWith('<svg')) {
    const base64 = btoa(unescape(encodeURIComponent(svg)));
    return `data:image/svg+xml;base64,${base64}`;
  }
  return null;
};

onMounted(() => {
  if (props.svgContent)
    imgSrc.value = toDataUrl(props.svgContent) as string | null;
});

watch(
  () => props.svgContent,
  (v) => {
    imgSrc.value = v ? toDataUrl(v) : null;
  }
);
</script>

<style scoped>
.two-factor-svg img {
  max-width: 260px;
}
</style>
