<template>
  <q-btn
    color="primary"
    unelevated
    no-caps
    icon="add"
    label="Add media"
    @click="show = true"
  />
  <GalleryUploadDialog v-model="show" />
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import GalleryUploadDialog from 'src/components/client/Gallery/GalleryUploadDialog.vue'

const show = ref(false)
</script>
