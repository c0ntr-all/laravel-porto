<template>
  <div
    ref="rootRef"
    class="ll-preset-rails"
    @mouseleave="clearHover"
  >
    <svg
      class="ll-preset-rails__svg"
      :width="PRESET_RAILS_WIDTH"
      :height="height"
      aria-hidden="true"
    >
      <g
        v-for="rail in rails"
        :key="rail.presetId"
        class="ll-preset-rails__group"
      >
        <path
          :d="rail.path"
          class="ll-preset-rails__path-hit"
          @mouseenter="event => setHover(rail, event)"
          @mousemove="event => setHover(rail, event)"
        />
        <path
          :d="rail.path"
          :stroke="rail.color"
          class="ll-preset-rails__path"
          :class="{ 'll-preset-rails__path--hovered': hoveredId === rail.presetId }"
          pointer-events="none"
        />
      </g>
    </svg>

    <div
      v-if="hoveredRail"
      class="ll-preset-rails__tooltip"
      :style="tooltipStyle"
    >
      {{ hoveredRail.title }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { IPost, IPreset } from 'src/types'
import {
  PRESET_RAIL_LANE_WIDTH,
  PRESET_RAILS_WIDTH,
  buildPresetRailPath,
  computePresetRailBounds
} from 'src/utils/LifeLog/presetRails'

interface RailViewModel {
  presetId: string
  title: string
  color: string
  path: string
  midY: number
}

const props = defineProps<{
  posts: IPost[]
  presets: IPreset[]
  anchors: Record<string, number>
  height: number
}>()

const rootRef = ref<HTMLElement | null>(null)
const hoveredId = ref<string | null>(null)
const tooltipTop = ref(0)

const rails = computed<RailViewModel[]>(() => {
  const bounds = computePresetRailBounds(props.posts, props.presets)

  return bounds
    .map((bound, index) => {
      const startY = props.anchors[bound.startPostId]
      const endY = props.anchors[bound.endPostId]

      if (startY == null || endY == null) {
        return null
      }

      const laneX = PRESET_RAILS_WIDTH - 8 - index * PRESET_RAIL_LANE_WIDTH

      return {
        presetId: bound.presetId,
        title: bound.title,
        color: bound.color,
        midY: (startY + endY) / 2,
        path: buildPresetRailPath(
          laneX,
          startY,
          endY,
          PRESET_RAILS_WIDTH,
          bound.roundEnd,
          'right'
        )
      }
    })
    .filter((item): item is RailViewModel => Boolean(item))
})

const hoveredRail = computed(() =>
  rails.value.find(rail => rail.presetId === hoveredId.value) ?? null
)

const tooltipStyle = computed(() => ({
  top: `${tooltipTop.value}px`
}))

function setHover(rail: RailViewModel, event: MouseEvent) {
  hoveredId.value = rail.presetId

  const root = rootRef.value
  if (!root) {
    return
  }

  const rootRect = root.getBoundingClientRect()
  tooltipTop.value = event.clientY - rootRect.top
}

function clearHover() {
  hoveredId.value = null
}
</script>

<style scoped lang="scss">
.ll-preset-rails {
  position: relative;
  height: 100%;

  &__svg {
    display: block;
    overflow: visible;
  }

  &__path {
    fill: none;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
    opacity: 0.72;
    transition: opacity 0.15s ease, stroke-width 0.15s ease, filter 0.15s ease;

    &--hovered {
      opacity: 1;
      stroke-width: 4;
      filter: brightness(1.18);
    }
  }

  &__path-hit {
    fill: none;
    stroke: transparent;
    stroke-width: 14;
    pointer-events: stroke;
    cursor: pointer;
  }

  &__tooltip {
    position: absolute;
    right: calc(100% + 10px);
    z-index: 2;
    transform: translateY(-50%);
    max-width: 220px;
    padding: 6px 10px;
    border-radius: 8px;
    background: rgba(30, 41, 59, 0.94);
    color: #fff;
    font-size: 12px;
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    pointer-events: none;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
  }
}
</style>
