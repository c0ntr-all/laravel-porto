<template>
  <div
    ref="root"
    class="app-slider"
    :class="{
      'app-slider--disabled': disable,
      'app-slider--dragging': dragging
    }"
    :style="{ width: width || '100%' }"
    role="slider"
    :aria-valuemin="min"
    :aria-valuemax="max"
    :aria-valuenow="displayValue"
    :aria-disabled="disable"
    :tabindex="disable ? -1 : 0"
    @pointerdown="onPointerDown"
    @keydown="onKeydown"
  >
    <div class="app-slider__rail">
      <div
        v-for="(range, index) in buffered"
        :key="index"
        class="app-slider__buffered"
        :style="bufferedStyle(range)"
      />
      <div
        class="app-slider__played"
        :style="{ width: `${toPercent(displayValue)}%` }"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'

export type SliderBufferedRange = {
  start: number
  end: number
}

interface Props {
  min?: number
  max?: number
  step?: number
  width?: string
  onlyDrop?: boolean
  disable?: boolean
  buffered?: SliderBufferedRange[]
}

const props = withDefaults(defineProps<Props>(), {
  min: 0,
  max: 100,
  step: 0.1,
  width: '100%',
  onlyDrop: false,
  disable: false,
  buffered: () => []
})

const emit = defineEmits<{
  change: [value: number]
  dragging: [value: boolean]
}>()

const model = defineModel<number>({ default: 0 })
const root = ref<HTMLElement | null>(null)
const dragging = ref(false)
const draftValue = ref(model.value)

const displayValue = computed(() => dragging.value ? draftValue.value : model.value)

watch(model, value => {
  if (!dragging.value) {
    draftValue.value = value
  }
})

const clamp = (value: number) => {
  const min = props.min
  const max = props.max
  const stepped = props.step > 0
    ? min + Math.round((value - min) / props.step) * props.step
    : value

  return Math.min(Math.max(stepped, min), max)
}

const toPercent = (value: number) => {
  const span = props.max - props.min
  if (span <= 0) {
    return 0
  }

  return ((value - props.min) / span) * 100
}

const bufferedStyle = (range: SliderBufferedRange) => {
  const start = Math.min(Math.max(range.start, props.min), props.max)
  const end = Math.min(Math.max(range.end, props.min), props.max)

  return {
    left: `${toPercent(start)}%`,
    width: `${Math.max(toPercent(end) - toPercent(start), 0)}%`
  }
}

const valueFromClientX = (clientX: number) => {
  const el = root.value
  if (!el) {
    return model.value
  }

  const rect = el.getBoundingClientRect()
  const ratio = rect.width <= 0 ? 0 : (clientX - rect.left) / rect.width
  return clamp(props.min + ratio * (props.max - props.min))
}

const commit = (value: number) => {
  const next = clamp(value)
  draftValue.value = next
  model.value = next
  emit('change', next)
}

const onPointerMove = (event: PointerEvent) => {
  draftValue.value = valueFromClientX(event.clientX)
  if (!props.onlyDrop) {
    model.value = draftValue.value
  }
}

const stopDragging = (event: PointerEvent) => {
  if (!dragging.value) {
    return
  }

  const el = root.value
  el?.removeEventListener('pointermove', onPointerMove)
  el?.removeEventListener('pointerup', stopDragging)
  el?.removeEventListener('pointercancel', stopDragging)
  window.removeEventListener('pointermove', onPointerMove)
  window.removeEventListener('pointerup', stopDragging)
  window.removeEventListener('pointercancel', stopDragging)

  const next = valueFromClientX(event.clientX)
  dragging.value = false
  emit('dragging', false)
  commit(next)

  if (el && event.pointerId != null) {
    try {
      el.releasePointerCapture(event.pointerId)
    } catch {
      // Capture may already be released.
    }
  }
}

const onPointerDown = (event: PointerEvent) => {
  if (props.disable || event.button !== 0) {
    return
  }

  event.preventDefault()
  dragging.value = true
  emit('dragging', true)
  draftValue.value = valueFromClientX(event.clientX)
  if (!props.onlyDrop) {
    model.value = draftValue.value
  }

  const el = root.value
  el?.setPointerCapture(event.pointerId)
  el?.addEventListener('pointermove', onPointerMove)
  el?.addEventListener('pointerup', stopDragging)
  el?.addEventListener('pointercancel', stopDragging)
  window.addEventListener('pointermove', onPointerMove)
  window.addEventListener('pointerup', stopDragging)
  window.addEventListener('pointercancel', stopDragging)
}

const onKeydown = (event: KeyboardEvent) => {
  if (props.disable) {
    return
  }

  const span = props.max - props.min
  const largeStep = Math.max(props.step, span / 10)
  let next = displayValue.value

  if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
    next -= props.step || 1
  } else if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
    next += props.step || 1
  } else if (event.key === 'PageDown') {
    next -= largeStep
  } else if (event.key === 'PageUp') {
    next += largeStep
  } else if (event.key === 'Home') {
    next = props.min
  } else if (event.key === 'End') {
    next = props.max
  } else {
    return
  }

  event.preventDefault()
  commit(next)
}

onBeforeUnmount(() => {
  const el = root.value
  el?.removeEventListener('pointermove', onPointerMove)
  el?.removeEventListener('pointerup', stopDragging)
  el?.removeEventListener('pointercancel', stopDragging)
  window.removeEventListener('pointermove', onPointerMove)
  window.removeEventListener('pointerup', stopDragging)
  window.removeEventListener('pointercancel', stopDragging)
})
</script>

<style lang="scss" scoped>
.app-slider {
  --slider-track: rgba(0, 0, 0, 0.16);
  --slider-buffered: rgba(25, 118, 210, 0.28);
  --slider-played: #1976d2;
  position: relative;
  display: flex;
  align-items: center;
  height: 16px;
  min-width: 48px;
  cursor: pointer;
  outline: none;
  touch-action: none;
  user-select: none;

  &--disabled {
    cursor: default;
    opacity: 0.45;
    pointer-events: none;
  }

  &:hover,
  &:focus-visible,
  &--dragging {
    .app-slider__rail {
      height: 6px;
    }
  }

  &:focus-visible {
    .app-slider__rail {
      box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.18);
    }
  }

  &__rail {
    position: relative;
    width: 100%;
    height: 4px;
    overflow: hidden;
    border-radius: 999px;
    background: var(--slider-track);
    transition: height 0.12s ease;
  }

  &__buffered,
  &__played {
    position: absolute;
    top: 0;
    bottom: 0;
    border-radius: inherit;
  }

  &__buffered {
    background: var(--slider-buffered);
  }

  &__played {
    left: 0;
    background: var(--slider-played);
  }
}
</style>
