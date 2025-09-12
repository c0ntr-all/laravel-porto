<template>
  <q-dialog
    v-model="show"
    :backdrop-filter="'brightness(60%)'"
  >
    <q-card style="width: 700px; max-width: 80vw;">
      <q-carousel
        v-model="currentSlide"
        class="text-white shadow-1 rounded-borders"
        transition-prev="scale"
        transition-next="scale"
        control-color="primary"
        swipeable
        animated
        arrows
      >
        <q-carousel-slide
          v-for="slide in slides"
          :key="slide.id"
          class="column no-wrap flex-center q-pa-none"
          :name="slide.id"
        >
          <q-img :src="slide.preview_thumb_path"/>
        </q-carousel-slide>
      </q-carousel>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { ref, watch, watchEffect } from 'vue'

interface Props {
  modelValue: any
  slides: any
  currentSlide: string
}

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()
const props = defineProps<Props>()
const show = ref(props.modelValue)
const currentSlide = ref(props.currentSlide)

watchEffect(() => {
  show.value = props.modelValue
  currentSlide.value = props.currentSlide
})
watch(show, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})
</script>

<style lang="scss" scoped>

</style>
