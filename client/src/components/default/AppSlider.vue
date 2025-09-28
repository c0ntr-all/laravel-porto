<template>
  <div class="app-slider" :style="{ width: width || '100%' }">
    <q-slider
      v-model="localValue"
      :min="0"
      :max="100"
      :step="0.1"
      :disable="disable"
      @update:model-value="handleUpdate"
      :class="{ 'slider--only-drop': onlyDrop }"
    />
  </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'

interface Props {
  data: number
  width?: string
  onlyDrop?: boolean
  disable?: boolean
}

interface Emits {
  (e: 'move', value: number): void
}

const props = withDefaults(defineProps<Props>(), {
  width: '100%',
  onlyDrop: false,
  disable: false
})

const emit = defineEmits<Emits>()

const localValue = ref(props.data)

watch(() => props.data, (newValue) => {
  localValue.value = newValue
})

const handleUpdate = (value: number | null) => {
  if (value !== null) {
    emit('move', value)
  }
}
</script>

<style lang="scss" scoped>
.app-slider {
  .slider--only-drop {
    pointer-events: none;
  }
}
</style>
