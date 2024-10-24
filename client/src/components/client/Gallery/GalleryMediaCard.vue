<template>
  <q-card class="media-card col-2">
    <q-img
      :src="media.path"
      :alt="media.name"
      :height="'100%'"
    />
  </q-card>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { MediaItem } from 'src/components/client/Gallery/types'

const props = defineProps<{
  media: MediaItem,
}>()
const media = ref(props.media)
</script>

<style lang="scss" scoped>

</style>
