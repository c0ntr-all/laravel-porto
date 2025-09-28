<template>
  <q-chip>{{ tag.name }}</q-chip>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ITag } from 'src/types/tag'

const props = defineProps<{
  tag: ITag
}>()
const tag = ref(props.tag)
</script>

<style scoped lang="scss">
</style>
