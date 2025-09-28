<template>
  <q-chip
    @click="emitSelect"
    @remove="emitRemove"
    :clickable="clickable"
    :removable="removable"
  >
    {{ tag.name }}
  </q-chip>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ITag } from 'src/types/tag'

const props = withDefaults(defineProps<{
  tag: ITag,
  removable?: boolean,
  clickable?: boolean
}>(), {
  removable: false,
  clickable: false
})
const emit = defineEmits<{
  (e: 'selected', tag: ITag): void
  (e: 'removed', tag: ITag): void
}>()
const tag = ref(props.tag)

const emitSelect = () => {
  emit('selected', tag.value)
}
const emitRemove = () => {
  emit('removed', tag.value)
}
</script>

<style scoped lang="scss">
</style>
