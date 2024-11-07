<template>
  <q-card-section>
    <div class="q-gutter-md">
      <q-input
        v-model="folderPath"
        label="Full path to the folder with images"
        outlined
      />
      <q-file
        v-model="files"
        label="Pick files"
        outlined
        use-chips
        multiple
        clearable
      />
    </div>
  </q-card-section>
  <q-card-section>
    <div class="row q-gutter-x-xs" style="border-left: 2px solid black">
      <template v-if="files">
        <div
          class="col-md-2 gallery-upload__images-item"
          v-for="file in files"
          :key="file"
        >
          <div class="gallery-upload__image">
            <img :src="imagePreview(file)" alt="">
          </div>
        </div>
      </template>
      <template v-else>
        <div style="color: #ccc">
          There are no selected files
        </div>
      </template>
    </div>
  </q-card-section>
  <q-card-section>
    <q-btn
      @click="initUpload"
      icon="upload"
      color="primary"
    >
      <div class="q-ml-xs">Begin upload</div>
    </q-btn>
  </q-card-section>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'initUpload', data: array, type: string): void
}>()
const files = ref()
const folderPath = ref('')

const imagePreview = (image: any) => {
  return URL.createObjectURL(image)
}

const initUpload = () => {
  const data = files.value.map(item => {
    return folderPath.value + '\\' + item.name
  })

  emit('initUpload', data, 'windows')
}
</script>

<style lang="scss" scoped>
.gallery-upload {
  &__image {
    img {
      width: 100px;
    }
  }
}
</style>
