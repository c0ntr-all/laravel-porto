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
      class="q-mb-lg"
      outlined
      dense
    />
  </form>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'

interface IUploadArtistResponse {
  meta: {
    message: string
  }
}

const fullPath = ref<string | null>(null)
const fullPathRef = ref()
const processLoading = ref(false)

const uploadArtist = async () => {
  processLoading.value = true
  fullPathRef.value.validate()

  await api.post<IUploadArtistResponse>('v1/music/artists/upload', {
    path: fullPath.value,
    is_preview: true
  }).then(response => {
    handleApiSuccess(response)
  }).catch(error => {
    handleApiError(error)
  }).finally(() => {
    processLoading.value = false
  })
}

const onReset = () => {
  fullPath.value = null
  fullPathRef.value.resetValidation()
}
</script>
