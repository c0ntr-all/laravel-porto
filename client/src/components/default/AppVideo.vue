<template>
  <video
    class="app-video"
    :src="src"
    :autoplay="autoplay"
    controls
  />
</template>

<script lang="ts" setup>
defineProps<{
  src: string
  autoplay: boolean
}>()
</script>

<style lang="scss" scoped>
.app-video {
  width: 100%;
  height: 100%;
}
</style>
