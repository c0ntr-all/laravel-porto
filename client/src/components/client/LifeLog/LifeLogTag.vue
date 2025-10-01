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
import { INewTag, ITag } from 'src/types/tag'

const props = withDefaults(defineProps<{
  tag: ITag | INewTag,
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
  (e: 'selected', tag: ITag): void
  (e: 'removed', tag: ITag | INewTag): void
}>()
const tag = ref(props.tag)

const emitSelect = () => {
  emit('selected', tag.value as ITag) // Only ITag can be selected
}
const emitRemove = () => {
  emit('removed', tag.value)
}
</script>

<style scoped lang="scss">
</style>
