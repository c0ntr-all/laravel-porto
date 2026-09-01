<template>
  <q-card class="widget-card" :class="[`widget-card--${widget.size}`, { 'widget-card--compact': compact, 'widget-card--editing': editing }]" flat>
    <div class="widget-card__head">
      <div class="widget-card__title">
        <q-icon :name="icon" size="18px" color="primary" />
        <span>{{ title }}</span>
      </div>

      <div v-if="editing" class="widget-card__actions">
        <q-btn-toggle
          :model-value="widget.size"
          dense
          unelevated
          no-caps
          toggle-color="primary"
          color="grey-3"
          text-color="grey-8"
          :options="sizeOptions"
          @update:model-value="emit('resize', $event)"
        />
        <q-btn flat dense round icon="arrow_upward" size="sm" @click="emit('move', -1)" />
        <q-btn flat dense round icon="arrow_downward" size="sm" @click="emit('move', 1)" />
        <q-btn v-if="hasConfig" flat dense round icon="tune" size="sm" @click="emit('configure')" />
        <q-btn flat dense round icon="delete" size="sm" color="negative" @click="emit('remove')" />
      </div>
    </div>

    <div class="widget-card__body">
      <component :is="viewComponent" :payload="widget.payload" />
    </div>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IDashboardWidget, IWidgetType } from 'src/types/Dashboard/dashboard'
import { WidgetSizeEnum } from 'src/enums/Dashboard/WidgetSizeEnum'
import { WidgetViewEnum } from 'src/enums/Dashboard/WidgetViewEnum'
import WelcomeWidget from './widgets/WelcomeWidget.vue'
import TextWidget from './widgets/TextWidget.vue'
import HtmlWidget from './widgets/HtmlWidget.vue'
import CountWidget from './widgets/CountWidget.vue'
import ListWidget from './widgets/ListWidget.vue'
import MediaWidget from './widgets/MediaWidget.vue'

const props = defineProps<{
  widget: IDashboardWidget
  definition?: IWidgetType
  editing?: boolean
  compact?: boolean
}>()

const emit = defineEmits<{
  resize: [size: string]
  move: [direction: -1 | 1]
  configure: []
  remove: []
}>()

const title = computed(() => props.widget.title || props.widget.payload?.title || props.definition?.name || props.widget.type)
const icon = computed(() => props.definition?.icon || 'widgets')
const hasConfig = computed(() => Object.keys(props.definition?.config_schema ?? {}).length > 0)

const sizeOptions = computed(() => {
  const supported = props.definition?.supported_sizes ?? [WidgetSizeEnum.Full, WidgetSizeEnum.Half, WidgetSizeEnum.Third]
  const labels: Record<string, string> = {
    [WidgetSizeEnum.Full]: 'Full',
    [WidgetSizeEnum.Half]: '1/2',
    [WidgetSizeEnum.Third]: '1/3'
  }

  return supported.map(size => ({ label: labels[size] ?? size, value: size }))
})

const viewComponent = computed(() => {
  const view = props.widget.payload?.view || props.definition?.view

  if (view === WidgetViewEnum.Welcome) return WelcomeWidget
  if (view === WidgetViewEnum.Text) return TextWidget
  if (view === WidgetViewEnum.Html) return HtmlWidget
  if (view === WidgetViewEnum.Count) return CountWidget
  if (view === WidgetViewEnum.Media) return MediaWidget

  return ListWidget
})
</script>

<style lang="scss" scoped>
.widget-card {
  display: flex;
  flex-direction: column;
  min-height: 160px;
  padding: 14px 16px 16px;
  border-radius: 16px;
  grid-column: span 6;

  &--full {
    grid-column: span 12;
  }

  &--half {
    grid-column: span 6;
  }

  &--third {
    grid-column: span 4;
  }

  &--compact {
    min-height: 132px;
    padding: 10px 12px 12px;
  }

  &--editing {
    outline: 1px dashed rgba(108, 95, 252, 0.45);
  }

  &__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
  }

  &__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #282f53;
  }

  &__actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 2px;
  }

  &__body {
    min-width: 0;
    flex: 1;
  }
}

@media (max-width: 1024px) {
  .widget-card--third {
    grid-column: span 6;
  }
}

@media (max-width: 720px) {
  .widget-card--full,
  .widget-card--half,
  .widget-card--third {
    grid-column: span 12;
  }
}
</style>
