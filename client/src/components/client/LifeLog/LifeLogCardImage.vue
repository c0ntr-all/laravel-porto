<template>
  <q-img
    :src="thumbSrc"
    class="media-item"
    fit="cover"
  >
    <template #error>
      <div class="absolute-full flex flex-center bg-grey-4 text-grey-8">
        <q-icon name="broken_image" size="24px" />
      </div>
    </template>
  </q-img>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IGalleryImage } from 'src/types/gallery'
import { getAttachmentThumbSrc } from 'src/utils/gallery'

const props = defineProps<{
  image: IGalleryImage
}>()

const thumbSrc = computed(() => getAttachmentThumbSrc(props.image))
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
