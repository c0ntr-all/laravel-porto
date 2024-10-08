<template>
  <q-dialog
    v-model="show"
    @update:modelValue="updateDialog"
  >
    <q-card style="width: 768px; max-width: 80vw;">
      <q-card-section>
        <p class="text-h6 q-ma-none">
          <slot name="header"/>
        </p>
      </q-card-section>

      <q-separator/>

      <q-card-section>
        <slot name="body"/>
      </q-card-section>

      <q-card-actions :align="actionsAlign" class="bg-white">
        <slot name="footer"/>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref, watchEffect, watch } from 'vue'

interface Props {
  modelValue: boolean
  actionsAlign?: 'right' | 'left' | 'center' | 'between' | 'around' | 'evenly' | 'stretch'
}

const props = withDefaults(defineProps<Props>(), {
  actionsAlign: () => 'right'
})
const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()
const actionsAlign = ref(props.actionsAlign)
const show = ref(props.modelValue)

watchEffect(() => {
  show.value = props.modelValue
})

watch(show, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})

const updateDialog = (value: boolean) => {
  emit('update:modelValue', value)
}
</script>

<style lang="scss" scoped>
</style>
