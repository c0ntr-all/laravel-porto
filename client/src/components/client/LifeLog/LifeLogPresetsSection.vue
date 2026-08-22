<template>
  <q-card flat bordered class="lifelog-presets-section">
    <q-card-section class="row items-center justify-between q-pb-sm">
      <div>
        <div class="text-subtitle1">Presets</div>
        <div class="text-caption text-grey-7">
          {{ presetsCount }} {{ presetsCountLabel }}
        </div>
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Создать preset"
        no-caps
        dense
        @click="openCreateModal"
      />
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pt-sm">
      <LifelogPresetsList
        @edit="openEditModal"
        @delete="confirmDelete"
      />
    </q-card-section>

    <AppModal
      v-model="isModalOpen"
      width="720px"
      scrollable
    >
      <template #header>
        {{ modalTitle }}
      </template>
      <template #body>
        <LifeLogPresetForm
          v-if="isModalOpen"
          :key="formKey"
          :preset-id="editingPresetId"
          @success="onFormSuccess"
        />
      </template>
    </AppModal>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'
import AppModal from 'src/components/default/AppModal.vue'
import LifelogPresetsList from 'src/components/client/LifeLog/LifelogPresetsList.vue'
import LifeLogPresetForm from 'src/components/client/LifeLog/LifeLogPresetForm.vue'
import { usePresetStore } from 'src/stores/modules/presetStore'
import { handleApiError } from 'src/utils/jsonapi'
import { IPreset } from 'src/types'

const $q = useQuasar()
const presetStore = usePresetStore()
const { presetsCount } = storeToRefs(presetStore)

const isModalOpen = ref(false)
const editingPresetId = ref<string | null>(null)
const formKey = ref(0)

const presetsCountLabel = computed(() => {
  const count = presetsCount.value
  const mod10 = count % 10
  const mod100 = count % 100

  if (mod10 === 1 && mod100 !== 11) return 'preset'
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return 'preset\'а'

  return 'preset\'ов'
})

const modalTitle = computed(() =>
  editingPresetId.value ? 'Редактировать preset' : 'Создать preset'
)

const openCreateModal = () => {
  editingPresetId.value = null
  formKey.value += 1
  isModalOpen.value = true
}

const openEditModal = (id: string) => {
  editingPresetId.value = id
  formKey.value += 1
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingPresetId.value = null
}

const onFormSuccess = () => {
  closeModal()
  presetStore.getPresets()
}

const confirmDelete = (preset: IPreset) => {
  $q.dialog({
    title: 'Удалить preset?',
    message: `Preset «${preset.title}» будет удалён без возможности восстановления.`,
    cancel: {
      label: 'Отмена',
      flat: true,
      noCaps: true
    },
    ok: {
      label: 'Удалить',
      color: 'negative',
      noCaps: true
    },
    persistent: true
  }).onOk(async () => {
    try {
      await presetStore.deletePreset(preset.id)
    } catch (error) {
      handleApiError(error)
    }
  })
}
</script>

<style lang="scss" scoped>
.lifelog-presets-section {
  width: 100%;
  background: #fff;
}
</style>
