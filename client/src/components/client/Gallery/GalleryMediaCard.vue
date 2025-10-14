<template>
  <q-card class="media-card col-2">
    <q-img
      class="media-card__image"
      :src="media.list_thumb_path"
      :alt="media.name"
      :height="'100%'"
    />
  </q-card>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { IMediaItem } from 'src/components/client/Gallery/types'

const props = defineProps<{
  media: IMediaItem,
}>()
const media = ref(props.media)
</script>

<style lang="scss" scoped>
.media-card {
  &:hover {
    cursor: pointer;
  }
  &__image {
    max-height: 176px;
  }
}
</style>
