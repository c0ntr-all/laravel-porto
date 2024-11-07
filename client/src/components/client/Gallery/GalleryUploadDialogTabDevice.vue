<template>
  <q-uploader
    style="width:100%"
    ref="uploader"
    :url="uploadEndpoint"
    :headers="uploadHeaders"
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
const uploadEndpoint = `${process.env.host}/v1/gallery/albums/1/media/upload`
const uploadHeaders = [{
  name: 'Authorization',
  value: `Bearer ${localStorage.access_token}`
}, {
  name: 'accept',
  value: 'application/json'
}]
</script>

<style scoped lang="scss">

</style>
