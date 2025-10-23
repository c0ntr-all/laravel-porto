<template>
  <q-dialog
    v-model="show"
    :backdrop-filter="'brightness(60%)'"
  >
    <q-card style="max-width: 80vw;" class="flex">
      <q-card-section :horizontal="false" class="carousel__scene" ref="sceneRef">
        <q-carousel
          v-model="currentSlideId"
          class="carousel text-white shadow-1"
          transition-prev="scale"
          transition-next="scale"
          transition-duration="50"
          control-color="primary"
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
      <q-card-section :horizontal="false">
        <div class="carousel__metadata metadata">
          zone for user, actions, comments, etc...
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, onMounted, onUnmounted, ref, watchEffect } from 'vue'
import { IAttachment } from 'src/types/attachment'

const props = defineProps<{
  slides: IAttachment[]
  currentSlideId: string
}>()
const show = defineModel<boolean>()
const currentSlideId = ref<string>(props.currentSlideId)

const sceneRef = ref<HTMLElement | null>(null)
const viewport = ref({ width: window.innerWidth, height: window.innerHeight })
const COMMENTS_WIDTH = 350
const FOOTER_HEIGHT = 100

const currentSlide = computed(() => {
  return props.slides.find((slide: IAttachment) => slide.id === currentSlideId.value)
})
const imageStyle = computed(() => {
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
    width: `${width}px`,
    height: `${height}px`
  }
})

watchEffect(() => {
  currentSlideId.value = props.currentSlideId
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
.carousel {
  max-width: none;
  max-height: none;

  &__scene {
    padding: 0;
  }
  &__metadata {
    width: 310px;
  }
}
</style>
