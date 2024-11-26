<template>
  <q-uploader
    style="width:100%"
    ref="uploader"
    :url="uploadEndpoint"
    :headers="uploadHeaders"
    @uploaded="processNewItems"
    field-name="file"
    multiple
  >
    <template v-slot:list="scope">
      <q-list separator>
        <q-item v-for="file in scope.files" :key="file.__key">
          <q-item-section>
            <q-item-label class="full-width ellipsis">
              {{ file.name }}
            </q-item-label>

            <q-item-label caption>
              Status: {{ file.__status }}
            </q-item-label>

            <q-item-label caption>
              {{ file.__sizeLabel }} / {{ file.__progressLabel }}
            </q-item-label>
          </q-item-section>

          <q-item-section
            v-if="file.__img"
            thumbnail
            class="gt-xs"
          >
            <img :src="file.__img.src">
          </q-item-section>

          <q-item-section top side>
            <q-btn
              class="gt-xs"
              size="12px"
              flat
              dense
              round
              icon="delete"
              @click="scope.removeFile(file)"
            />
          </q-item-section>
        </q-item>
      </q-list>
    </template>
  </q-uploader>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { IMediaItem } from 'src/components/client/Gallery/types'

const albumId = inject<string>('albumId')
const addMediaToAlbum = inject<{(media: IMediaItem[]): void }>('addMediaToAlbum')

const uploadEndpoint = `${process.env.host}/v1/gallery/albums/${albumId}/media/upload`
const uploadHeaders = [{
  name: 'Authorization',
  value: `Bearer ${localStorage.access_token}`
}, {
  name: 'accept',
  value: 'application/json'
}]

const processNewItems = (info: any) => {
  const jsonResponse = JSON.parse(info.xhr.responseText)
  if (addMediaToAlbum) {
    addMediaToAlbum([{
      id: jsonResponse.data.id,
      ...jsonResponse.data.attributes
    }])
  }
}
</script>

<style scoped lang="scss">

</style>
