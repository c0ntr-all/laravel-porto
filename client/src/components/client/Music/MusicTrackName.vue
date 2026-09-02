<template>
  <span class="music-track-name">
    <span class="music-track-name__title">{{ name }}</span>
    <span
      v-if="normalizedCredits"
      class="music-track-name__credits"
    >{{ normalizedCredits }}</span>
  </span>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

const props = defineProps<{
  name: string
  credits?: string | null
}>()

const normalizedCredits = computed(() => {
  const value = props.credits?.trim()
  return value || ''
})
</script>

<style lang="scss" scoped>
.music-track-name {
  min-width: 0;
  white-space: nowrap;
}

.music-track-name__credits {
  margin-left: 0.4em;
  color: #5c6773;
  font-weight: 400;
  font-size: 0.92em;
}
</style>
