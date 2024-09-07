<template>
  <q-btn
    icon="arrow_back"
    color="primary"
    :to="link"
  >
    <div class="q-ml-xs">{{ text }}</div>
  </q-btn>
</template>

<script lang="ts" setup>
const props = defineProps<{
  text: string,
  link: string
}>()
const text = props.text
const link = props.link
</script>

<style scoped lang="scss">

</style>
