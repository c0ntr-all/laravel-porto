<template>
  <q-chip
    @click="emitSelect"
    @remove="emitRemove"
    :clickable="clickable"
    :removable="removable"
    :dense="dense"
    :color="color"
    :textColor="textColor"
  >
    {{ tag.name }}
  </q-chip>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ITag } from 'src/types/tag'

const props = withDefaults(defineProps<{
  tag: any,
  removable?: boolean,
  clickable?: boolean,
  dense?: boolean,
  color?: string,
  textColor?: string
}>(), {
  removable: false,
  clickable: false,
  dense: false,
  color: '',
  textColor: ''
})
const emit = defineEmits<{
  (e: 'selected', tag: any): void
  (e: 'removed', tag: any): void
}>()
const tag = ref(props.tag)

const emitSelect = () => {
  emit('selected', tag.value as ITag) // Only ITag can be selected
}
const emitRemove = () => {
  if ('id' in props.tag) {
    // emit ITag
    emit('removed', tag.value)
  } else {
    // emit INewTag
    emit('removed', tag.value)
  }
  emit('removed', tag.value)
}
</script>

<style scoped lang="scss">
</style>
