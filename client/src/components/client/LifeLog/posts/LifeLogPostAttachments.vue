<template>
  <div class="ll-post-attachments">
    <button
      v-for="attachment in attachments"
      :key="attachment.id"
      type="button"
      class="ll-post-attachments__item"
      @click="openCarousel(attachment.id)"
    >
      <LifeLogCardImage
        v-if="!isGalleryVideo(attachment)"
        :image="attachment"
      />
      <LifeLogCardVideo
        v-else
        :image="attachment"
      />
    </button>

    <GalleryCarousel
      v-model="showCarousel"
      v-model:current-slide-id="currentSlideId"
      :slides="attachments"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { IPost } from 'src/types'
import { isGalleryVideo } from 'src/utils/gallery'
import LifeLogCardImage from 'src/components/client/LifeLog/LifeLogCardImage.vue'
import LifeLogCardVideo from 'src/components/client/LifeLog/forms/LifeLogCardVideo.vue'
import GalleryCarousel from 'src/components/client/Gallery/GalleryCarousel.vue'

const props = defineProps<{
  post: IPost
}>()

const attachments = computed(() => props.post.attachments ?? [])

const showCarousel = ref(false)
const currentSlideId = ref('')

function openCarousel(id: string) {
  currentSlideId.value = id
  showCarousel.value = true
}
</script>

<style scoped lang="scss">
.ll-post-attachments {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
  gap: 8px;

  &__item {
    appearance: none;
    border: 0;
    padding: 0;
    background: transparent;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    aspect-ratio: 1 / 1;

    :deep(.media-item) {
      width: 100%;
      height: 100%;
      max-height: none;
      border-radius: 8px;
    }
  }
}
</style>
