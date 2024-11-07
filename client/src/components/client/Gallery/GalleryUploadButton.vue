<template>
  <q-btn
    @click="handleUploadButton"
    icon="upload"
    color="primary"
  >
    <div class="q-ml-xs">Upload images</div>
  </q-btn>
  <GalleryUploadDialog v-model="show"/>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import GalleryUploadDialog from 'src/components/client/Gallery/GalleryUploadDialog.vue'

const show = ref(false)

const handleUploadButton = () => {
  show.value = true
}
</script>

<style lang="scss" scoped>

</style>
