<template>
  <div class="text-h6 q-mb-md">Artists upload</div>
  <form @submit.prevent.stop="uploadArtist" @reset.prevent.stop="onReset" class="q-gutter-md">
    <q-btn
      type="submit"
      :loading="processLoading"
      label="Upload"
      color="primary"
      class="q-mb-lg"
    />
    <q-btn type="reset" label="Reset" class="q-mb-lg"/>
    <q-input
      v-model="fullPath"
      ref="fullPathRef"
      :rules="[
        (val: any) => !!val || 'Field must not be empty',
        (val: any) => val.length >= 10 || '10 symbols min'
      ]"
      lazy-rules
      label="Folder path"
      class="q-mb-md"
      outlined
      dense
    />
  </form>

  <MusicLibraryFolderTree v-model="fullPath" class="q-mb-lg" />

  <MusicUploadProgress />

  <div class="text-h6 q-mb-md">Upload sessions</div>
  <MusicUploadsTable />
</template>
<script lang="ts" setup>
import { onUnmounted, ref } from 'vue'
import type { QInput } from 'quasar'
import { useMusicUploadStore } from 'src/stores/modules/musicUploadStore'
import MusicUploadsTable from 'src/components/admin/Music/MusicUploadsTable.vue'
import MusicUploadProgress from 'src/components/admin/Music/MusicUploadProgress.vue'
import MusicLibraryFolderTree from 'src/components/admin/Music/MusicLibraryFolderTree.vue'

const store = useMusicUploadStore()
const fullPath = ref<string | null>(null)
const fullPathRef = ref<QInput | null>(null)
const processLoading = ref(false)

const uploadArtist = async () => {
  const isValid = await fullPathRef.value?.validate()
  if (!isValid || !fullPath.value) {
    return
  }

  processLoading.value = true

  try {
    await store.createUpload(fullPath.value)
  } finally {
    processLoading.value = false
  }
}

const onReset = () => {
  fullPath.value = null
  fullPathRef.value?.resetValidation()
}

onUnmounted(() => {
  store.stopWatching()
})
</script>
