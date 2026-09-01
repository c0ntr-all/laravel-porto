<template>
  <q-card flat bordered class="ll-filter-panel">
    <q-card-section class="q-pb-sm">
      <div class="text-subtitle2 q-mb-sm">Фильтры</div>

      <q-input
        v-model="draft.search"
        dense
        outlined
        clearable
        label="Поиск по тексту"
        debounce="300"
      >
        <template #prepend>
          <q-icon name="search" />
        </template>
      </q-input>

      <div class="row q-col-gutter-sm q-mt-sm">
        <div class="col-12 col-sm-6">
          <AppDatetimeField v-model="dateFromModel" />
          <div class="text-caption text-grey-7 q-mt-xs">Дата с</div>
        </div>
        <div class="col-12 col-sm-6">
          <AppDatetimeField v-model="dateToModel" />
          <div class="text-caption text-grey-7 q-mt-xs">Дата по</div>
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-py-sm">
      <div class="text-caption text-grey-7 q-mb-xs">Presets</div>
      <div class="row q-gutter-xs">
        <q-chip
          v-for="preset in presets"
          :key="preset.id"
          clickable
          dense
          :outline="draft.activePresetId !== preset.id"
          :color="draft.activePresetId === preset.id ? undefined : 'grey-3'"
          :style="presetChipStyle(preset)"
          @click="applyPreset(preset)"
        >
          {{ preset.title }}
        </q-chip>
        <span v-if="!presets.length" class="text-caption text-grey-6">
          Нет presets
        </span>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-py-sm">
      <div class="text-caption text-grey-7 q-mb-xs">Выбранные теги</div>
      <div class="row q-gutter-xs q-mb-sm">
        <LifeLogTag
          v-for="tag in selectedTags"
          :key="tag.id"
          :tag="tag"
          removable
          @removed="removeTag"
        />
        <span v-if="!selectedTags.length" class="text-caption text-grey-6">
          Теги не выбраны
        </span>
      </div>

      <div class="text-caption text-grey-7 q-mb-xs">Доступные теги</div>
      <div class="row q-gutter-xs">
        <LifeLogTag
          v-for="tag in availableTags"
          :key="tag.id"
          :tag="tag"
          clickable
          @selected="selectTag"
        />
      </div>
    </q-card-section>

    <q-card-section class="row items-center justify-between q-pt-none">
      <q-select
        v-model="draft.tags_mode"
        :options="tagsModes"
        dense
        outlined
        label="Режим тегов"
        style="min-width: 120px"
      />

      <div class="row q-gutter-sm">
        <q-btn
          outline
          color="grey-7"
          label="Сброс"
          no-caps
          @click="reset"
        />
        <q-btn
          color="primary"
          label="Применить"
          no-caps
          @click="submit"
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { unique } from 'radash'
import { ILifeLogFilter, IPreset } from 'src/types'
import { ITag } from 'src/types/tag'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import { mapPresetToLifeLogFilter } from 'src/utils/LifeLog/filter.mapper'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'

const props = defineProps<{
  modelValue: ILifeLogFilter
  allTags: ITag[]
  presets: IPreset[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: ILifeLogFilter]
  submit: [value: ILifeLogFilter]
  reset: []
}>()

const tagsModes = ['or', 'and']
const draft = ref<ILifeLogFilter>(createEmptyLifeLogFilter())

watch(
  () => props.modelValue,
  value => {
    draft.value = {
      ...value,
      tags: [...value.tags]
    }
  },
  { immediate: true, deep: true }
)

const selectedTags = computed(() =>
  unique([...draft.value.tags], tag => tag.id)
)

const dateFromModel = computed({
  get: () => draft.value.date_from ?? '',
  set: value => {
    draft.value.date_from = value || null
  }
})

const dateToModel = computed({
  get: () => draft.value.date_to ?? '',
  set: value => {
    draft.value.date_to = value || null
  }
})

const availableTags = computed(() =>
  props.allTags.filter(tag => !selectedTags.value.some(selected => selected.id === tag.id))
)

function selectTag(tag: ITag) {
  draft.value.tags = [...draft.value.tags, tag]
}

function removeTag(tag: ITag) {
  draft.value.tags = draft.value.tags.filter(item => item.id !== tag.id)
}

function applyPreset(preset: IPreset) {
  draft.value = mapPresetToLifeLogFilter(preset, props.allTags)
  submit()
}

function presetChipStyle(preset: IPreset) {
  if (draft.value.activePresetId !== preset.id) {
    return {}
  }

  return {
    backgroundColor: preset.color || '#90a4ae',
    color: '#fff'
  }
}

function submit() {
  emit('update:modelValue', { ...draft.value, tags: [...draft.value.tags] })
  emit('submit', { ...draft.value, tags: [...draft.value.tags] })
}

function reset() {
  draft.value = createEmptyLifeLogFilter()
  emit('update:modelValue', createEmptyLifeLogFilter())
  emit('reset')
}
</script>

<style scoped lang="scss">
.ll-filter-panel {
  background: #fff;
}
</style>
