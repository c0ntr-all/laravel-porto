<template>
  <q-img
    :src="image.list_thumb_path"
    class="media-item"
  >
    <template v-slot:default>
      <q-chip class="media-item__time" text-color="white" dense>0:19</q-chip>
      <q-img class="media-item__img" src="~assets/icons/video-square.svg" fit="contain" />
    </template>
    <template v-slot:error>
      <div class="absolute-full flex flex-center bg-negative text-white">
        Error
      </div>
    </template>
  </q-img>
</template>

<script lang="ts" setup>
import { IGalleryImage } from 'src/types/gallery'

defineProps<{
  image: IGalleryImage
}>()
</script>

<style lang="scss" scoped>
.media-item {
  position: relative;
  max-height: 150px;
  cursor: pointer;

  &:hover {

  }

  &__img {
    position: absolute;
    width: 30px;
    height: 30px;
    top: 50%;
    left: 50%;
    margin: -15px 0 0 -15px;
    background: none;
  }

  &__time {
    padding: 0 6px;
    font-size: 10px;
  }
}
.lifelog-card__video {

}
</style>
