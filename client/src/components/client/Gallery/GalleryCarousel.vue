<template>
  <q-dialog
    v-model="show"
    :backdrop-filter="'brightness(60%)'"
  >
    <q-card class="photo-viewer">
      <div class="photo-viewer__main">
        <q-card-section
          :horizontal="false"
          class="photo-viewer__scene"
          ref="sceneRef"
          :style="`width: ${minContainerWidth}px`"
        >
          <q-carousel
            ref="carousel"
            v-model="currentSlideId"
            class="carousel text-white shadow-1"
            transition-prev="scale"
            transition-next="scale"
            transition-duration="50"
            height="100%"
            style="max-width: none"
            :fullscreen="false"
            swipeable
            arrows
          >
            <q-carousel-slide
              v-for="slide in slides"
              :key="slide.id"
              class="column no-wrap flex-center q-pa-none"
              :name="slide.id"
            >
              <AppVideo
                v-if="isGalleryVideo(slide) && slide.id === currentSlideId"
                :src="resolveMediaUrl(slide.original_path)"
                :autoplay="true"
              />
              <q-img
                v-else
                :src="resolveMediaUrl(
                  isGalleryVideo(slide)
                    ? (slide.list_thumb_path || slide.preview_thumb_path)
                    : slide.preview_thumb_path
                )"
                :style="imageStyle"
                fit="contain"
              />
            </q-carousel-slide>
          </q-carousel>
        </q-card-section>

        <GalleryViewerActions :item="currentSlide" />
      </div>

      <q-card-section class="photo-viewer__metadata" :horizontal="false">
        <GalleryViewerComments
          :commentable-id="currentCommentableId"
          :commentable-type="currentCommentableType"
          :description="currentSlide?.description"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup generic="T extends IImageSource">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { IImageSource } from 'src/types/carousel'
import AppVideo from 'src/components/default/AppVideo.vue'
import GalleryViewerComments from 'src/components/client/Gallery/GalleryViewerComments.vue'
import GalleryViewerActions from 'src/components/client/Gallery/GalleryViewerActions.vue'
import {
  galleryCommentableType,
  isGalleryVideo,
  resolveMediaUrl
} from 'src/utils/gallery'

const props = defineProps<{
  slides: T[]
}>()

const show = defineModel<boolean>()
const currentSlideId = defineModel<string>('currentSlideId')

const sceneRef = ref<HTMLElement | null>(null)
const viewport = ref({ width: window.innerWidth, height: window.innerHeight })
const COMMENTS_WIDTH = 320
const FOOTER_HEIGHT = 56
const INITIAL_SCENE_WIDTH = 600
let minWidthState = INITIAL_SCENE_WIDTH

const currentSlide = computed(() => {
  return props.slides.find((slide: IImageSource) => String(slide.id) === String(currentSlideId.value))
})

const currentCommentableId = computed(() => (
  currentSlide.value ? String(currentSlide.value.id) : undefined
))

const currentCommentableType = computed(() => (
  currentSlide.value ? galleryCommentableType(currentSlide.value) : undefined
))

const minContainerWidth = computed(() => {
  if (imageSizes.value.width && imageSizes.value.width > minWidthState) {
    minWidthState = imageSizes.value.width
  }

  return minWidthState
})

const imageStyle = computed(() => {
  return {
    width: `${imageSizes.value.width}px`,
    height: `${imageSizes.value.height}px`
  }
})

const imageSizes = computed(() => {
  if (!currentSlide.value) return {}

  const maxW = viewport.value.width - COMMENTS_WIDTH - 40
  const maxH = viewport.value.height - FOOTER_HEIGHT - 40

  const img = currentSlide.value
  const aspect = img.width && img.height ? img.width / img.height : 16 / 9
  const maxAspect = maxW / maxH

  let width, height

  if (aspect > maxAspect) {
    width = maxW
    height = width / aspect
  } else {
    height = maxH
    width = height * aspect
  }

  return {
    width: Math.round(width),
    height: Math.round(height)
  }
})

watch(show, (value) => {
  if (!value) {
    minWidthState = INITIAL_SCENE_WIDTH
  }
})

onMounted(() => {
  window.addEventListener('resize', updateViewport)
})

const updateViewport = () => {
  viewport.value = { width: window.innerWidth, height: window.innerHeight }
}

onUnmounted(() => {
  window.removeEventListener('resize', updateViewport)
})
</script>

<style lang="scss" scoped>
.photo-viewer {
  display: flex;
  max-width: 95vw;
  min-width: 910px;
  max-height: 95vh;
  overflow: hidden;

  &__main {
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
    background: #111;
  }

  &__scene {
    flex: 1 1 auto;
    min-width: 600px;
    min-height: 450px;
    padding: 0;
  }

  &__metadata {
    display: flex;
    flex-direction: column;
    flex: 0 0 320px;
    width: 320px;
    min-height: 0;
    overflow: hidden;
    padding: 16px;
    background: #fff;
  }
}

.carousel {
  max-width: none;
  max-height: none;
  background: #222222 !important;
}
</style>
