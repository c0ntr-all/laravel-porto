<template>
  <q-dialog
    v-model="show"
    :backdrop-filter="'brightness(60%)'"
  >
    <q-card class="photo-viewer">
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
          swipeable
          arrows
        >
          <q-carousel-slide
            v-for="slide in slides"
            :key="slide.id"
            class="column no-wrap flex-center q-pa-none"
            :name="slide.id"
          >
            <q-img
              :src="slide.preview_thumb_path"
              :style="imageStyle"
              fit="contain"
            />
          </q-carousel-slide>
        </q-carousel>
      </q-card-section>
      <q-card-section class="photo-viewer__metadata metadata" :horizontal="false">
        zone for user, actions, comments, etc...
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { IAttachment } from 'src/types/attachment'

// --- Props ---
const props = defineProps<{
  slides: IAttachment[]
}>()

// --- Models ---
const show = defineModel<boolean>()
const currentSlideId = defineModel<string>('currentSlideId')

// --- Refs ---
const sceneRef = ref<HTMLElement | null>(null)

// --- State ---
const viewport = ref({ width: window.innerWidth, height: window.innerHeight })
const COMMENTS_WIDTH = 350
const FOOTER_HEIGHT = 100
const INITIAL_SCENE_WIDTH = 600
let minWidthState = INITIAL_SCENE_WIDTH

// --- Computed ---
const currentSlide = computed(() => {
  return props.slides.find((slide: IAttachment) => slide.id === currentSlideId.value)
})
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
  const aspect = img.width / img.height
  const maxAspect = maxW / maxH

  let width, height

  if (aspect > maxAspect) {
    // горизонтальное фото
    width = maxW
    height = width / aspect
  } else {
    // вертикальное фото
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

// проверяем ориентацию всех слайдов
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

  &__scene {
    flex: 1 1 auto;
    min-width: 600px;
    min-height: 450px;
    padding: 0;
  }
  &__metadata {
    flex: 0 0 auto;
    width: 310px;
  }
}
.carousel {
  max-width: none;
  max-height: none;
  background: #222222 !important;
}
</style>
