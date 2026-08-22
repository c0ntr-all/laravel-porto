<template>
  <div class="lifelog-preset-form">
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
      class="q-mb-lg"
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
        label="Создать preset"
        color="primary"
        :disable="!isSaveAvailable"
        :loading="isSubmitting"
        @click="processCreatePreset"
      />
    </div>
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
import { formatPostDatetime } from 'src/api/mappers/LifeLog/preset.mapper'
import { IPresetModel } from 'src/types'

const emit = defineEmits<{
  success: []
}>()

const {
  startPresetPost,
  endPresetPost,
  resetPreset,
  createPreset
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
const isSubmitting = ref(false)

const isSaveAvailable = computed(() => !!model.value.title?.trim())

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

onMounted(() => {
  if (startPresetPost.value) {
    dateFrom.value = formatPostDatetime(startPresetPost.value)
  }
  if (endPresetPost.value) {
    dateTo.value = formatPostDatetime(endPresetPost.value)
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
  model.value = createBaseModel()
}

const processCreatePreset = async () => {
  if (!isSaveAvailable.value) return

  isSubmitting.value = true

  try {
    await createPreset({
      title: model.value.title.trim(),
      color: model.value.color,
      date_from: dateFrom.value.trim() || null,
      date_to: dateTo.value.trim() || null,
      tags: selectedTags.value.length ? [...selectedTags.value] : undefined
    })

    resetForm()
    emit('success')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.lifelog-preset-form {
  width: 100%;
}
</style>
