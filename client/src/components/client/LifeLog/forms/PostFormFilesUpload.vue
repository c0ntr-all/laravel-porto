<template>
  <q-file
    v-model="attachmentModel"
    label="Pick files"
    outlined
    use-chips
    multiple
    clearable
    dense
  />

  <div class="row q-gutter-x-xs" style="border-left: 2px solid black">
    <template v-if="attachmentModel?.length">
      <div
        class="col-md-2 post-form__files-item"
        v-for="file in attachmentModel"
        :key="file"
      >
        <div class="post-form__file">
          <img :src="getImagePreview(file)" alt="">
        </div>
      </div>
    </template>
    <template v-else>
      <div style="color: #ccc">
        There are no selected files
      </div>
    </template>
  </div>
</template>

<script lang="ts" setup>
import { onUnmounted, watch } from 'vue'

const attachmentModel = defineModel()

// Локальное хранилище ObjectURL, чтобы потом освободить
const objectUrls: string[] = []

/**
 * Создаёт превью для File и сохраняет URL для последующей очистки
 */
const getImagePreview = (file: File): string => {
  const url = URL.createObjectURL(file)
  objectUrls.push(url)
  return url
}

/**
 * Удаляет файл из files
 */
// const removeImage = (index: number): void => {
//   console.log(index)
//   return
//   const removed = attachmentModel.splice(index, 1)
//   // Удаляем preview, если он был создан
//   if (removed[0]) {
//     objectUrls.forEach(url => URL.revokeObjectURL(url))
//   }
// }

/**
 * Следим за изменениями списка изображений, чтобы очищать старые превью
 */
watch(
  () => attachmentModel,
  () => {
    objectUrls.forEach(URL.revokeObjectURL)
    objectUrls.length = 0
  }
)

onUnmounted(() => {
  objectUrls.forEach(URL.revokeObjectURL)
})
</script>

<style lang="scss" scoped>
.post-form {
  &__file {
    img {
      width: 100px;
    }
  }
}
</style>
