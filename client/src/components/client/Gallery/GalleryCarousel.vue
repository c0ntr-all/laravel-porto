<template>
  <q-dialog
    v-model="show"
    :backdrop-filter="'brightness(60%)'"
  >
    <q-card class="photo-viewer">
      <div class="photo-viewer__main">
        <q-card-section
          class="photo-viewer__scene"
          :horizontal="false"
          :style="sceneStyle"
        >
          <q-carousel
            v-model="currentSlideId"
            class="carousel text-white"
            transition-prev="scale"
            transition-next="scale"
            transition-duration="50"
            height="100%"
            :fullscreen="false"
            swipeable
            :arrows="false"
          >
            <q-carousel-slide
              v-for="slide in slides"
              :key="slide.id"
              class="photo-viewer__slide column no-wrap flex-center q-pa-none"
              :name="slide.id"
            >
              <div class="photo-viewer__frame">
                <AppVideo
                  v-if="isGalleryVideo(slide) && isCurrentSlide(slide)"
                  class="photo-viewer__video"
                  :src="resolveMediaUrl(slide.original_path)"
                  :autoplay="true"
                />
                <q-img
                  v-else
                  class="photo-viewer__image"
                  :src="resolveMediaUrl(
                    isGalleryVideo(slide)
                      ? (slide.list_thumb_path || slide.preview_thumb_path)
                      : slide.preview_thumb_path
                  )"
                  fit="contain"
                />

                <template v-if="isCurrentSlide(slide) && !isGalleryVideo(slide)">
                  <button
                    v-if="canGoPrev"
                    type="button"
                    class="photo-viewer__hit photo-viewer__hit--prev"
                    aria-label="Previous"
                    @click.stop="goPrev"
                  />
                  <button
                    v-if="canGoNext"
                    type="button"
                    class="photo-viewer__hit photo-viewer__hit--next"
                    aria-label="Next"
                    @click.stop="goNext"
                  />
                </template>
              </div>
            </q-carousel-slide>
          </q-carousel>

          <button
            v-if="canGoPrev"
            type="button"
            class="photo-viewer__arrow photo-viewer__arrow--prev"
            aria-label="Previous"
            @click.stop="goPrev"
          >
            <q-icon name="chevron_left" size="28px" />
          </button>
          <button
            v-if="canGoNext"
            type="button"
            class="photo-viewer__arrow photo-viewer__arrow--next"
            aria-label="Next"
            @click.stop="goNext"
          >
            <q-icon name="chevron_right" size="28px" />
          </button>
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

const viewport = ref({ width: window.innerWidth, height: window.innerHeight })
const COMMENTS_WIDTH = 320
const FOOTER_HEIGHT = 56
const DIALOG_INSET = 48
const MIN_SCENE_HEIGHT = 450
const INITIAL_SCENE_WIDTH = 600
const lockedSceneWidth = ref(INITIAL_SCENE_WIDTH)

const currentSlide = computed(() => {
  return props.slides.find((slide: IImageSource) => isCurrentSlide(slide))
})

const currentIndex = computed(() => (
  props.slides.findIndex((slide: IImageSource) => isCurrentSlide(slide))
))

const canGoPrev = computed(() => currentIndex.value > 0)
const canGoNext = computed(() => (
  currentIndex.value >= 0 && currentIndex.value < props.slides.length - 1
))

const currentCommentableId = computed(() => (
  currentSlide.value ? String(currentSlide.value.id) : undefined
))

const currentCommentableType = computed(() => (
  currentSlide.value ? galleryCommentableType(currentSlide.value) : undefined
))

function availableMaxWidth(): number {
  return Math.max(240, viewport.value.width - DIALOG_INSET - COMMENTS_WIDTH)
}

function availableMaxHeight(): number {
  return Math.max(180, viewport.value.height - DIALOG_INSET - FOOTER_HEIGHT)
}

function fitCurrentMedia(maxW: number, maxH: number): { width: number; height: number } {
  if (!currentSlide.value) {
    return {
      width: Math.min(INITIAL_SCENE_WIDTH, maxW),
      height: Math.min(MIN_SCENE_HEIGHT, maxH)
    }
  }

  const img = currentSlide.value
  const aspect = img.width && img.height ? img.width / img.height : 16 / 9
  const maxAspect = maxW / maxH

  let width: number
  let height: number

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
}

const sceneSize = computed(() => {
  const maxW = availableMaxWidth()
  const maxH = availableMaxHeight()
  const fitted = fitCurrentMedia(maxW, maxH)
  const width = Math.min(Math.max(lockedSceneWidth.value, fitted.width), maxW)
  const height = Math.min(maxH, Math.max(fitted.height, Math.min(MIN_SCENE_HEIGHT, maxH)))

  return {
    width: Math.round(width),
    height: Math.round(height)
  }
})

watch(
  [currentSlide, viewport, show],
  () => {
    if (!show.value) {
      lockedSceneWidth.value = INITIAL_SCENE_WIDTH
      return
    }

    const fitted = fitCurrentMedia(availableMaxWidth(), availableMaxHeight())

    if (fitted.width > lockedSceneWidth.value) {
      lockedSceneWidth.value = fitted.width
    }
  }
)

const sceneStyle = computed(() => ({
  width: `${sceneSize.value.width}px`,
  height: `${sceneSize.value.height}px`,
  maxWidth: `calc(95vw - ${COMMENTS_WIDTH}px)`,
  maxHeight: `calc(95vh - ${FOOTER_HEIGHT}px)`
}))

function isCurrentSlide(slide: IImageSource): boolean {
  return String(slide.id) === String(currentSlideId.value)
}

function goTo(index: number): void {
  const slide = props.slides[index]

  if (!slide) {
    return
  }

  currentSlideId.value = String(slide.id)
}

function goPrev(): void {
  if (canGoPrev.value) {
    goTo(currentIndex.value - 1)
  }
}

function goNext(): void {
  if (canGoNext.value) {
    goTo(currentIndex.value + 1)
  }
}

function updateViewport(): void {
  viewport.value = { width: window.innerWidth, height: window.innerHeight }
}

onMounted(() => {
  window.addEventListener('resize', updateViewport)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateViewport)
})
</script>

<style lang="scss" scoped>
.photo-viewer {
  display: flex;
  max-width: 95vw;
  max-height: 95vh;
  min-width: 0;
  overflow: hidden;

  &__main {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
    max-width: calc(95vw - 320px);
    background: #111;
  }

  &__scene {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
    min-height: 0;
    padding: 0;
    overflow: hidden;
  }

  &__slide,
  &__frame {
    width: 100%;
    height: 100%;
  }

  &__frame {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  &__image {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;

    :deep(.q-img__container) {
      height: 100%;
    }

    :deep(.q-img__image) {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
  }

  &__video {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    background: #000;
  }

  &__hit {
    position: absolute;
    top: 0;
    bottom: 0;
    z-index: 1;
    width: 50%;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;

    &--prev {
      left: 0;
    }

    &--next {
      right: 0;
    }
  }

  &__arrow {
    position: absolute;
    top: 50%;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    color: #fff;
    background: rgba(0, 0, 0, 0.45);
    transform: translateY(-50%);
    cursor: pointer;

    &:hover {
      background: rgba(0, 0, 0, 0.7);
    }

    &--prev {
      left: 12px;
    }

    &--next {
      right: 12px;
    }
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
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  overflow: hidden;
  background: #222222 !important;
}

:deep(.q-carousel__slide) {
  padding: 0;
}

:deep(.photo-viewer__video.app-video),
:deep(.app-video) {
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
</style>
