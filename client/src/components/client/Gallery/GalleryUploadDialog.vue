<template>
  <q-dialog v-model="show">
    <q-card class="gallery-upload">
      <q-card-section class="gallery-upload__header">
        <div>
          <div class="gallery-upload__title">Add media</div>
          <div class="gallery-upload__subtitle">Photos and videos from device, link, or a local path</div>
        </div>
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>

      <q-card-section class="gallery-upload__sources q-pt-none">
        <q-btn-toggle
          v-model="tab"
          class="gallery-upload__toggle"
          toggle-color="primary"
          unelevated
          no-caps
          spread
          :options="sourceOptions"
        />
      </q-card-section>

      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <q-tab-panel name="device" class="q-pa-none">
          <GalleryUploadDialogTabDevice @done="show = false" />
        </q-tab-panel>
        <q-tab-panel name="web" class="q-pa-none">
          <GalleryUploadDialogTabWeb @done="show = false" />
        </q-tab-panel>
        <q-tab-panel name="windows" class="q-pa-none">
          <GalleryUploadDialogTabWindows @done="show = false" />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import GalleryUploadDialogTabWindows from 'src/components/client/Gallery/GalleryUploadDialogTabWindows.vue'
import GalleryUploadDialogTabWeb from 'src/components/client/Gallery/GalleryUploadDialogTabWeb.vue'
import GalleryUploadDialogTabDevice from 'src/components/client/Gallery/GalleryUploadDialogTabDevice.vue'

const show = defineModel<boolean>({ default: false })
const tab = ref<'device' | 'web' | 'windows'>('device')

const sourceOptions = [
  { label: 'Device', value: 'device', icon: 'upload_file' },
  { label: 'Link', value: 'web', icon: 'link' },
  { label: 'Local path', value: 'windows', icon: 'folder_open' }
]

watch(show, (visible) => {
  if (visible) {
    tab.value = 'device'
  }
})
</script>

<style lang="scss" scoped>
.gallery-upload {
  width: 640px;
  max-width: 92vw;
  border-radius: 16px;

  &__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  &__title {
    font-size: 20px;
    font-weight: 600;
    color: #282f53;
    line-height: 1.3;
  }

  &__subtitle {
    margin-top: 2px;
    font-size: 13px;
    color: #777a8f;
  }

  &__toggle {
    width: 100%;
    border-radius: 12px;
    background: #f0f0f5;
  }
}
</style>
