<template>
  <div class="ll-timeline-graph">
    <div class="ll-timeline-graph__header row items-center justify-between q-mb-sm">
      <div class="text-subtitle2">Граф диапазонов</div>
      <div class="text-caption text-grey-7">
        {{ posts.length }} {{ posts.length === 1 ? 'пост' : 'постов' }}
      </div>
    </div>

    <div
      v-if="!posts.length"
      class="ll-timeline-graph__empty text-caption text-grey-6"
    >
      Недостаточно данных для построения графа
    </div>

    <div
      v-else
      class="ll-timeline-graph__canvas"
    >
      <svg
        :viewBox="`0 0 ${layout.width} ${layout.height}`"
        preserveAspectRatio="xMinYMin meet"
        class="ll-timeline-graph__svg"
      >
        <line
          :x1="layout.trunkX"
          y1="8"
          :x2="layout.trunkX"
          :y2="layout.height - 8"
          class="ll-timeline-graph__trunk"
        />

        <path
          v-for="range in layout.ranges"
          :key="range.id"
          :d="range.path"
          :stroke="range.color"
          class="ll-timeline-graph__branch"
        >
          <title>{{ range.title }}</title>
        </path>

        <g
          v-for="node in layout.nodes"
          :key="node.id"
          class="ll-timeline-graph__node"
        >
          <circle
            :cx="layout.trunkX"
            :cy="node.y"
            r="6"
          />
          <text
            :x="layout.trunkX + 12"
            :y="node.y + 4"
            class="ll-timeline-graph__label"
          >
            {{ node.title }}
          </text>
          <title>{{ node.dateLabel }}</title>
        </g>
      </svg>
    </div>

    <div
      v-if="layout.ranges.length"
      class="ll-timeline-graph__legend q-mt-sm"
    >
      <div
        v-for="range in layout.ranges"
        :key="`legend-${range.id}`"
        class="ll-timeline-graph__legend-item row items-center no-wrap q-mb-xs"
      >
        <span
          class="ll-timeline-graph__legend-dot"
          :style="{ backgroundColor: range.color }"
        />
        <span class="text-caption">{{ range.title }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IPost, IPreset } from 'src/types'
import { buildTimelineLayout } from 'src/utils/LifeLog/timeline'

const props = defineProps<{
  posts: IPost[]
  presets: IPreset[]
}>()

const layout = computed(() => buildTimelineLayout(props.posts, props.presets))
</script>

<style scoped lang="scss">
.ll-timeline-graph {
  background: #fff;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  padding: 12px;

  &__empty {
    padding: 16px 8px;
    text-align: center;
    border: 1px dashed #d8dee6;
    border-radius: 8px;
  }

  &__canvas {
    overflow-x: auto;
  }

  &__svg {
    width: 100%;
    min-width: 260px;
    height: auto;
    display: block;
  }

  &__trunk {
    stroke: #94a3b8;
    stroke-width: 2;
  }

  &__branch {
    fill: none;
    stroke-width: 3;
    stroke-linecap: round;
    opacity: 0.9;
  }

  &__node circle {
    fill: #fff;
    stroke: #334155;
    stroke-width: 2;
  }

  &__label {
    font-size: 11px;
    fill: #475569;
  }

  &__legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 8px;
    flex-shrink: 0;
  }
}
</style>
