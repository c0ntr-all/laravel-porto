<template>
  <q-dialog v-model="show">
    <q-card style="width: 700px; max-width: 80vw;">
      <q-card-section>
        <q-carousel
          v-model="currentSlide"
          class="text-white shadow-1 rounded-borders"
          transition-prev="scale"
          transition-next="scale"
          control-color="primary"
          swipeable
          animated
          padding
          arrows
        >
          <q-carousel-slide
            v-for="slide in slides"
            :key="slide.id"
            class="column no-wrap flex-center"
            :name="slide.id"
          >
            <q-img :src="slide.path"/>
          </q-carousel-slide>
        </q-carousel>
      </q-card-section>
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
