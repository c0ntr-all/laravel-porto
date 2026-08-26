<template>
  <div class="app-slider" :style="{ width: width || '100%' }">
    <q-slider
      :model-value="displayValue"
      :min="min"
      :max="max"
      :step="step"
      :disable="disable"
      :color="color"
      @update:model-value="onInput"
      @change="onChange"
      @pan="onPan"
    />
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'

interface Props {
  min?: number
  max?: number
  step?: number
  width?: string
  onlyDrop?: boolean
  disable?: boolean
  color?: string
}

const props = withDefaults(defineProps<Props>(), {
  min: 0,
  max: 100,
  step: 0.1,
  width: '100%',
  onlyDrop: false,
  disable: false,
  color: 'primary'
})

const emit = defineEmits<{
  change: [value: number]
  dragging: [value: boolean]
}>()

const model = defineModel<number>({ default: 0 })
const dragging = ref(false)
const draftValue = ref(model.value)
const handledByPan = ref(false)

const displayValue = computed(() => dragging.value ? draftValue.value : model.value)

watch(model, value => {
  if (!dragging.value) {
    draftValue.value = value
  }
})

const commit = (value: number) => {
  draftValue.value = value

  if (props.onlyDrop || model.value !== value) {
    model.value = value
  }

  emit('change', value)
}

const onInput = (value: number | null) => {
  if (value === null) {
    return
  }

  draftValue.value = value

  if (!props.onlyDrop) {
    model.value = value
  }
}

const onPan = (phase: 'start' | 'end') => {
  if (phase === 'start') {
    dragging.value = true
    handledByPan.value = false
    draftValue.value = model.value
    emit('dragging', true)
    return
  }

  dragging.value = false
  handledByPan.value = true
  emit('dragging', false)
  commit(draftValue.value)
}

const onChange = (value: number | null) => {
  if (value === null) {
    return
  }

  if (handledByPan.value) {
    handledByPan.value = false
    return
  }

  commit(value)
}
</script>
