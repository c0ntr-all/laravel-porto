<template>
  <div class="lifelog-preset-form q-pa-md">
    <div class="text-h6 q-mb-md">Создать период</div>
    <div class="q-mb-md flex" style="column-gap: .25rem">
      <q-input
        name="preset"
        ref="titleRef"
        v-model="model.title"
        class="q-pa-none"
        style="flex-grow: 1"
        label="Title of preset"
        :rules="[val => !!val || 'Field is required']"
        dense
        outlined
      />
      <AppColorPicker v-model="model.color" />
    </div>
    <div class="flex justify-between items-center">
      <div class="lifelog-presets-table">
        <div class="lifelog-presets-table__row">
          <div class="lifelog-presets-table__col">Start Post</div>
          <div v-if="startPresetPost" class="lifelog-presets-table__col">
            {{ startPresetPost.id }}. {{ startPresetPost.title }} ({{ startPresetPost.date }} {{ startPresetPost.time }})
          </div>
          <div v-else class="lifelog-presets-table__col lifelog-presets-table__col--empty">
            Не выбрано
          </div>
        </div>
        <div class="lifelog-presets-table__row">
          <div class="lifelog-presets-table__col">End Post</div>
          <div v-if="endPresetPost" class="lifelog-presets-table__col">
            {{ endPresetPost.id }}. {{ endPresetPost.title }} ({{ endPresetPost.date }} {{ endPresetPost.time }})
          </div>
          <div v-else class="lifelog-presets-table__col lifelog-presets-table__col--empty">
            Не выбрано
          </div>
        </div>
      </div>
      <div class="lifelog-presets-actions">
        <q-btn
          class="q-mt-none q-ml-md"
          color="grey"
          label="Reset"
          @click="resetPreset"
          :disable="!isSaveAvailable"
          outline
        />
        <q-btn
          label="Создать"
          color="primary"
          @click="processCreatePreset"
          :disable="!isSaveAvailable"
        />
      </div>
    </div>
    <hr>
    <LifelogPresetsList />
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import useLifelogPresets from 'src/composables/client/Lifelog/useLifelogPreset'
import AppColorPicker from 'src/components/default/AppColorPicker.vue'
import { generateRandomHex } from 'src/utils/colors'
import { IPresetModel } from 'src/types'
import LifelogPresetsList from 'src/components/client/LifeLog/LifelogPresetsList.vue'

const { startPresetPost, endPresetPost, resetPreset, createPreset } = useLifelogPresets()

const baseModel: IPresetModel = {
  title: null,
  description: null,
  start_post_id: null,
  end_post_id: null,
  color: generateRandomHex(),
  icon: null
}

const model = ref<IPresetModel>({ ...baseModel })

const isSaveAvailable = computed(() => startPresetPost.value !== null)

const processCreatePreset = () => {
  const data = {
    ...model.value,
    start_post_id: startPresetPost.value.id,
    end_post_id: endPresetPost.value.id
  }
  createPreset(data).then(() => {
    clearModel()
    resetPreset()
  })
}

const clearModel = () => {
  baseModel.color = generateRandomHex()
  model.value = baseModel
}
</script>

<style lang="scss" scoped>
.lifelog-preset-form {
  width: 100%;
  background-color: #ffffff;
}
.lifelog-presets-table {
  &__row {
    display: flex;
    column-gap: 1rem;
  }
  &__col {
    &:first-child {
      width: 62px;
      text-align: right;
      font-weight: bold;
    }

    &--empty {
      color: #aaa;
    }
  }
}
.lifelog-presets-actions {
  display: flex;
  column-gap: 1rem;
}
</style>
