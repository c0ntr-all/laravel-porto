<template>
  <div class="ll-post-attachments">
    <div
      v-if="mediaAttachments.length"
      class="ll-post-attachments__media"
    >
      <button
        v-for="attachment in mediaAttachments"
        :key="attachment.id"
        type="button"
        class="ll-post-attachments__item"
        @click="openCarousel(attachment.id)"
      >
        <LifeLogCardImage
          v-if="!isGalleryVideoAttachment(attachment)"
          :image="attachment"
        />
        <LifeLogCardVideo
          v-else
          :image="attachment"
        />
      </button>
    </div>

    <div
      v-if="documentAttachments.length"
      class="ll-post-attachments__documents"
    >
      <LifeLogPostDocumentItem
        v-for="document in documentAttachments"
        :key="document.id"
        :document="document"
      />
    </div>

    <GalleryCarousel
      v-if="mediaAttachments.length"
      v-model="showCarousel"
      v-model:current-slide-id="currentSlideId"
      :slides="mediaAttachments"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { IPost, IPostDocumentAttachment } from 'src/types'
import { isGalleryVideoAttachment, splitPostAttachments } from 'src/utils/attachment'
import LifeLogCardImage from 'src/components/client/LifeLog/LifeLogCardImage.vue'
import LifeLogCardVideo from 'src/components/client/LifeLog/forms/LifeLogCardVideo.vue'
import LifeLogPostDocumentItem from 'src/components/client/LifeLog/posts/LifeLogPostDocumentItem.vue'
import GalleryCarousel from 'src/components/client/Gallery/GalleryCarousel.vue'

const props = defineProps<{
  post: IPost
}>()

const attachments = computed(() => props.post.attachments ?? [])
const splitAttachments = computed(() => splitPostAttachments(attachments.value))
const mediaAttachments = computed(() => splitAttachments.value.media)
const documentAttachments = computed(() =>
  splitAttachments.value.documents as IPostDocumentAttachment[]
)

const showCarousel = ref(false)
const currentSlideId = ref('')

function openCarousel(id: string) {
  currentSlideId.value = id
  showCarousel.value = true
}
</script>

<style scoped lang="scss">
.ll-post-attachments {
  display: flex;
  flex-direction: column;
  gap: 10px;

  &__media {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
    gap: 8px;
  }

  &__documents {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

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
