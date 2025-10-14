<template>
  <q-img
    :src="image.preview_thumb_path"
    style="max-width: 222px"
  >
    <template v-slot:error>
      <div class="absolute-full flex flex-center bg-negative text-white">
        Cannot load image
      </div>
    </template>
  </q-img>
</template>

<script lang="ts" setup>
defineProps<{
  image: object
}>()
</script>

<style lang="scss" scoped>

</style>
