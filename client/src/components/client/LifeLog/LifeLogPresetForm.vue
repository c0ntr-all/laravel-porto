<template>
  <div class="lifelog-preset-form">
    <q-inner-loading :showing="isLoading">
      <q-spinner size="40px" color="primary" />
    </q-inner-loading>

    <template v-if="!isLoading">
      <div class="row q-col-gutter-md q-mb-md">
        <div class="col-grow">
          <q-input
            ref="titleRef"
            v-model="model.title"
            label="Название preset"
            :rules="[val => !!val?.trim() || 'Обязательное поле']"
            dense
            outlined
          />
        </div>
        <div class="col-auto flex items-center">
          <AppColorPicker v-model="model.color" />
        </div>
      </div>

      <div class="row q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md-6">
          <PresetDateBlock
            label="Начало"
            icon="flag"
            :post="startPresetPost"
            v-model="dateFrom"
            @clear-post="clearStartPost"
          />
        </div>
        <div class="col-12 col-md-6">
          <PresetDateBlock
            label="Окончание"
            icon="outlined_flag"
            :post="endPresetPost"
            v-model="dateTo"
            @clear-post="clearEndPost"
          />
        </div>
      </div>

      <PresetTagsSelect
        v-model="selectedTags"
        class="q-mb-md"
      />

      <q-select
        v-model="selectedContentTypes"
        :options="contentTypeOptions"
        label="Тип поста"
        class="q-mb-lg"
        multiple
        emit-value
        map-options
        outlined
        dense
        clearable
        use-chips
        hint="Не выбрано — подходят посты любого типа"
      />

      <q-banner dense rounded class="bg-blue-1 text-primary q-mb-md">
        <template #avatar>
          <q-icon name="info" />
        </template>
        Дата и время задаются в одном поле: выбор поста подставит его дату, после чего значение можно скорректировать вручную.
      </q-banner>

      <div class="row justify-end q-gutter-sm">
        <q-btn
          flat
          no-caps
          label="Сбросить"
          color="grey"
          @click="resetForm"
        />
        <q-btn
          no-caps
          :label="submitLabel"
          color="primary"
          :disable="!isSaveAvailable"
          :loading="isSubmitting"
          @click="processSubmit"
        />
      </div>
    </template>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue'
import useLifelogPresets from 'src/composables/client/Lifelog/useLifelogPreset'
import { usePresetStore } from 'src/stores/modules/presetStore'
import AppColorPicker from 'src/components/default/AppColorPicker.vue'
import PresetDateBlock from 'src/components/client/LifeLog/PresetDateBlock.vue'
import PresetTagsSelect from 'src/components/client/LifeLog/PresetTagsSelect.vue'
import { generateRandomHex } from 'src/utils/colors'
import { formatPostDatetime, mapPresetToFormModel } from 'src/api/mappers/LifeLog/preset.mapper'
import { handleApiError } from 'src/utils/jsonapi'
import { IPreset, IPresetModel } from 'src/types'
import {
  POST_CONTENT_TYPES,
  POST_CONTENT_TYPE_LABELS,
  PostContentTypeEnum
} from 'src/enums/LifeLog/PostContentTypeEnum'

const props = defineProps<{
  presetId?: string | null
}>()

const emit = defineEmits<{
  success: []
}>()

const {
  startPresetPost,
  endPresetPost,
  resetPreset,
  createPreset,
  updatePreset
} = useLifelogPresets()

const presetStore = usePresetStore()

const createBaseModel = (): IPresetModel => ({
  title: '',
  color: generateRandomHex()
})

const model = ref<IPresetModel>(createBaseModel())
const dateFrom = ref('')
const dateTo = ref('')
const selectedTags = ref<string[]>([])
const selectedContentTypes = ref<PostContentTypeEnum[]>([])
const isSubmitting = ref(false)
const isLoading = ref(false)

const contentTypeOptions = POST_CONTENT_TYPES.map(value => ({
  value,
  label: POST_CONTENT_TYPE_LABELS[value]
}))

const isEditMode = computed(() => Boolean(props.presetId))
const isSaveAvailable = computed(() => !!model.value.title?.trim())
const submitLabel = computed(() => isEditMode.value ? 'Сохранить' : 'Создать preset')

const buildPayload = (): IPresetModel => ({
  title: model.value.title.trim(),
  color: model.value.color,
  date_from: dateFrom.value.trim() || null,
  date_to: dateTo.value.trim() || null,
  tags: selectedTags.value.length ? [...selectedTags.value] : undefined,
  content_type: selectedContentTypes.value.length
    ? [...selectedContentTypes.value]
    : []
})

const populateForm = (preset: IPreset) => {
  const formModel = mapPresetToFormModel(preset)

  model.value = {
    title: formModel.title,
    color: formModel.color
  }

  presetStore.setStartPresetPostId(preset.start_post_id)
  presetStore.setEndPresetPostId(preset.end_post_id)

  dateFrom.value = formModel.date_from ?? ''
  dateTo.value = formModel.date_to ?? ''
  selectedTags.value = formModel.tags ?? []
  selectedContentTypes.value = formModel.content_type ?? []
}

const applyPostDatesIfNeeded = () => {
  if (startPresetPost.value) {
    dateFrom.value = formatPostDatetime(startPresetPost.value)
  }
  if (endPresetPost.value) {
    dateTo.value = formatPostDatetime(endPresetPost.value)
  }
}

watch(startPresetPost, post => {
  if (post) {
    dateFrom.value = formatPostDatetime(post)
  }
}, { flush: 'sync' })

watch(endPresetPost, post => {
  if (post) {
    dateTo.value = formatPostDatetime(post)
  }
}, { flush: 'sync' })

onMounted(async () => {
  if (!props.presetId) {
    applyPostDatesIfNeeded()
    return
  }

  isLoading.value = true

  try {
    const preset = await presetStore.getPreset(props.presetId)
    populateForm(preset)
  } catch (error) {
    handleApiError(error)
  } finally {
    isLoading.value = false
  }
})

const clearStartPost = () => {
  presetStore.setStartPresetPostId(null)
}

const clearEndPost = () => {
  presetStore.setEndPresetPostId(null)
}

const resetForm = () => {
  resetPreset()
  dateFrom.value = ''
  dateTo.value = ''
  selectedTags.value = []
  selectedContentTypes.value = []
  model.value = createBaseModel()
}

const processSubmit = async () => {
  if (!isSaveAvailable.value) return

  isSubmitting.value = true

  try {
    const payload = buildPayload()

    if (isEditMode.value && props.presetId) {
      await updatePreset(props.presetId, payload)
    } else {
      await createPreset(payload)
      resetForm()
    }

    emit('success')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.lifelog-preset-form {
  width: 100%;
  position: relative;
  min-height: 120px;
}
</style>
