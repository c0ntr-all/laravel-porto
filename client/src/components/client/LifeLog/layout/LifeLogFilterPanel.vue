<template>
  <q-card flat bordered class="ll-filter-panel">
    <q-expansion-item
      v-model="expanded.search"
      icon="search"
      label="Поиск"
      header-class="ll-filter-panel__header"
      default-opened
    >
      <q-card-section class="q-pt-none">
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
      </q-card-section>
    </q-expansion-item>

    <q-separator />

    <q-expansion-item
      v-model="expanded.dates"
      icon="event"
      label="Даты"
      header-class="ll-filter-panel__header"
      default-opened
    >
      <q-card-section class="q-pt-none">
        <div class="row q-col-gutter-sm">
          <div class="col-12 col-sm-6">
            <AppDatetimeField
              v-if="!draft.ignore_time"
              v-model="dateFromModel"
            />
            <AppDateField
              v-else
              v-model="dateFromModel"
            />
            <div class="text-caption text-grey-7 q-mt-xs">Дата с</div>
          </div>
          <div class="col-12 col-sm-6">
            <AppDatetimeField
              v-if="!draft.ignore_time"
              v-model="dateToModel"
            />
            <AppDateField
              v-else
              v-model="dateToModel"
            />
            <div class="text-caption text-grey-7 q-mt-xs">Дата по</div>
          </div>
        </div>

        <q-checkbox
          v-model="draft.ignore_time"
          class="q-mt-sm"
          label="не учитывать время"
          @update:model-value="handleIgnoreTimeChange"
        />
      </q-card-section>
    </q-expansion-item>

    <q-separator />

    <q-expansion-item
      v-model="expanded.tags"
      icon="label"
      label="Теги"
      header-class="ll-filter-panel__header"
      default-opened
    >
      <q-card-section class="q-pt-none">
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
    </q-expansion-item>

    <q-separator />

    <q-card-section class="row items-center justify-between q-pt-sm">
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
import { computed, reactive, ref, watch } from 'vue'
import { unique } from 'radash'
import { ILifeLogFilter } from 'src/types'
import { ITag } from 'src/types/tag'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import AppDateField from 'src/components/default/AppDateField.vue'
import { toDateOnly } from 'src/utils/LifeLog/post'

const props = defineProps<{
  modelValue: ILifeLogFilter
  allTags: ITag[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: ILifeLogFilter]
  submit: [value: ILifeLogFilter]
  reset: []
}>()

const tagsModes = ['or', 'and']
const draft = ref<ILifeLogFilter>(createEmptyLifeLogFilter())
const expanded = reactive({
  search: true,
  dates: true,
  tags: true
})

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

function handleIgnoreTimeChange(ignoreTime: boolean) {
  if (!ignoreTime) {
    return
  }

  if (draft.value.date_from) {
    draft.value.date_from = toDateOnly(draft.value.date_from)
  }

  if (draft.value.date_to) {
    draft.value.date_to = toDateOnly(draft.value.date_to)
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

  :deep(.ll-filter-panel__header) {
    min-height: 44px;
    font-weight: 600;
  }
}
</style>
