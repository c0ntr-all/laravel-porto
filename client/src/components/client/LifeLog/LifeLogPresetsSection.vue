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
      <LifelogPresetsList />
    </q-card-section>

    <AppModal
      v-model="isCreateModalOpen"
      width="720px"
      scrollable
    >
      <template #header>
        Создать preset
      </template>
      <template #body>
        <LifeLogPresetForm
          v-if="isCreateModalOpen"
          @success="onPresetCreated"
        />
      </template>
    </AppModal>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import AppModal from 'src/components/default/AppModal.vue'
import LifelogPresetsList from 'src/components/client/LifeLog/LifelogPresetsList.vue'
import LifeLogPresetForm from 'src/components/client/LifeLog/LifeLogPresetForm.vue'
import { usePresetStore } from 'src/stores/modules/presetStore'

const presetStore = usePresetStore()
const { presetsCount } = storeToRefs(presetStore)

const isCreateModalOpen = ref(false)

const presetsCountLabel = computed(() => {
  const count = presetsCount.value
  const mod10 = count % 10
  const mod100 = count % 100

  if (mod10 === 1 && mod100 !== 11) return 'preset'
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return 'preset\'а'

  return 'preset\'ов'
})

const openCreateModal = () => {
  isCreateModalOpen.value = true
}

const onPresetCreated = () => {
  isCreateModalOpen.value = false
  presetStore.getPresets()
}
</script>

<style lang="scss" scoped>
.lifelog-presets-section {
  width: 100%;
  background: #fff;
}
</style>
