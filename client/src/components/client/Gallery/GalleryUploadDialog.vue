<template>
  <q-dialog v-model="show" @hide="clearModel">
    <q-card class="gallery-upload" style="width: 700px; max-width: 80vw;">
      <q-tabs
        v-model="tab"
        dense
        class="bg-grey-2 text-grey-7"
        active-color="primary"
        indicator-color="purple"
        align="justify"
      >
        <q-tab name="windows" label="Windows" icon="desktop_windows" />
        <q-tab name="web" label="Web" icon="public" />
        <q-tab name="upload" label="Upload" icon="file_upload" />
      </q-tabs>

      <q-tab-panels
        class="text-dark text-center"
        style=" min-height: 213px"
        v-model="tab"
        animated
      >
        <q-tab-panel name="windows">
          <GalleryUploadDialogTabWindows @initUpload="uploadImages" />
        </q-tab-panel>

        <q-tab-panel name="web">
          <GalleryUploadDialogTabWeb @initUpload="uploadImages" />
        </q-tab-panel>

        <q-tab-panel name="upload">
          <GalleryUploadDialogTabDevice @initUpload="uploadImages" />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { inject, ref, watch, watchEffect } from 'vue'
import GalleryUploadDialogTabWindows from 'src/components/client/Gallery/GalleryUploadDialogTabWindows.vue'
import GalleryUploadDialogTabWeb from 'src/components/client/Gallery/GalleryUploadDialogTabWeb.vue'
import GalleryUploadDialogTabDevice from 'src/components/client/Gallery/GalleryUploadDialogTabDevice.vue'

interface Props {
  modelValue: any
}

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()
const props = defineProps<Props>()
const show = ref(props.modelValue)
const tab = ref('windows')

const uploadImages = inject<{(data: [] | string, type: string): Promise<void>}>('uploadImages')

watchEffect(() => {
  show.value = props.modelValue
})
watch(show, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})
</script>

<style lang="scss" scoped>
</style>
