<template>
  <q-img
    :src="image.preview_thumb_path"
    class="lifelog-card__image"
  >
    <template v-slot:error>
      <div class="absolute-full flex flex-center bg-negative text-white">
        Error
      </div>
    </template>
  </q-img>
</template>

<script lang="ts" setup>
import { IAttachment } from 'src/types/attachment'

defineProps<{
  image: IAttachment
}>()
</script>

<style lang="scss" scoped>
.lifelog-card__image {
  max-height: 150px;
  cursor: pointer;
}
</style>
