<template>
  <div class="upload-device">
    <div
      class="upload-device__dropzone"
      :class="{
        'upload-device__dropzone--active': isDragging,
        'upload-device__dropzone--disabled': galleryStore.isUploading
      }"
      @dragenter.prevent="onDragEnter"
      @dragover.prevent="onDragEnter"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
      @click="openFilePicker"
    >
      <q-icon name="cloud_upload" size="36px" color="primary" />
      <div class="upload-device__drop-title">Drop photos and videos here</div>
      <div class="upload-device__drop-caption">or click to browse this device</div>
    </div>

    <input
      ref="fileInput"
      class="hidden"
      type="file"
      :accept="GALLERY_MEDIA_ACCEPT"
      multiple
      @change="onFileInput"
    >

    <div v-if="items.length" class="upload-device__list">
      <div
        v-for="item in items"
        :key="item.id"
        class="upload-device__item"
      >
        <div class="upload-device__preview">
          <video
            v-if="isVideoFile(item.file)"
            :src="previewUrl(item.id)"
            muted
          />
          <img
            v-else
            :src="previewUrl(item.id)"
            :alt="item.file.name"
          >
        </div>

        <div class="upload-device__info">
          <div class="upload-device__name" :title="item.file.name">{{ item.file.name }}</div>
          <div class="upload-device__meta">
            {{ isVideoFile(item.file) ? 'Video' : 'Photo' }}
            · {{ formatSize(item.file.size) }}
          </div>
          <q-linear-progress
            v-if="item.status === 'uploading' || item.status === 'done'"
            class="q-mt-xs"
            :value="item.progress / 100"
            :color="item.status === 'done' ? 'positive' : 'primary'"
            rounded
          />
          <div v-if="item.status === 'error'" class="text-negative text-caption">
            {{ item.error || 'Upload failed' }}
          </div>
        </div>

        <q-btn
          icon="close"
          flat
          round
          dense
          :disable="galleryStore.isUploading"
          @click.stop="removeItem(item.id)"
        />
      </div>
    </div>

    <div class="upload-device__actions">
      <q-btn
        flat
        no-caps
        label="Clear"
        :disable="!items.length || galleryStore.isUploading"
        @click="clearItems"
      />
      <q-btn
        color="primary"
        unelevated
        no-caps
        :label="uploadLabel"
        :loading="galleryStore.isUploading"
        :disable="!pendingCount"
        @click="upload"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onUnmounted, ref } from 'vue'
import { Notify } from 'quasar'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { mapFileToUploadItem } from 'src/api/mappers/gallery.mapper'
import { IUploadItem } from 'src/types/gallery'
import { GALLERY_MEDIA_ACCEPT, isMediaFile, isVideoFile } from 'src/utils/gallery'

const emit = defineEmits<{
  done: []
}>()

const galleryStore = useGalleryStore()

const fileInput = ref<HTMLInputElement | null>(null)
const items = ref<IUploadItem[]>([])
const isDragging = ref(false)
const objectUrls = new Map<string, string>()

const pendingCount = computed(() => (
  items.value.filter(item => item.status === 'pending' || item.status === 'error').length
))

const uploadLabel = computed(() => {
  if (!pendingCount.value) {
    return 'Upload'
  }

  return `Upload ${pendingCount.value} ${pendingCount.value === 1 ? 'file' : 'files'}`
})

function openFilePicker(): void {
  if (galleryStore.isUploading) {
    return
  }

  fileInput.value?.click()
}

function onDragEnter(): void {
  if (!galleryStore.isUploading) {
    isDragging.value = true
  }
}

function onFileInput(event: Event): void {
  const input = event.target as HTMLInputElement
  addFiles(Array.from(input.files ?? []))
  input.value = ''
}

function onDrop(event: DragEvent): void {
  isDragging.value = false

  if (galleryStore.isUploading) {
    return
  }

  addFiles(Array.from(event.dataTransfer?.files ?? []))
}

function addFiles(files: File[]): void {
  const accepted = files.filter(isMediaFile)
  const skipped = files.length - accepted.length

  if (skipped > 0) {
    Notify.create({
      type: 'warning',
      message: `${skipped} file(s) skipped. Only photos and videos are allowed.`
    })
  }

  for (const file of accepted) {
    const item = mapFileToUploadItem(file)
    objectUrls.set(item.id, URL.createObjectURL(file))
    items.value.push(item)
  }
}

function previewUrl(id: string): string {
  return objectUrls.get(id) ?? ''
}

function removeItem(id: string): void {
  revokeUrl(id)
  items.value = items.value.filter(item => item.id !== id)
}

function clearItems(): void {
  objectUrls.forEach(url => URL.revokeObjectURL(url))
  objectUrls.clear()
  items.value = []
}

function revokeUrl(id: string): void {
  const url = objectUrls.get(id)

  if (url) {
    URL.revokeObjectURL(url)
    objectUrls.delete(id)
  }
}

function formatSize(bytes: number): string {
  if (bytes < 1024) {
    return `${bytes} B`
  }

  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} KB`
  }

  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

async function upload(): Promise<void> {
  const uploaded = await galleryStore.uploadDeviceFiles(items.value)

  if (uploaded.length && items.value.every(item => item.status === 'done' || item.status === 'canceled')) {
    clearItems()
    emit('done')
  }
}

onUnmounted(() => {
  objectUrls.forEach(url => URL.revokeObjectURL(url))
  objectUrls.clear()
})
</script>

<style lang="scss" scoped>
.upload-device {
  padding: 16px 20px 20px;

  &__dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-height: 148px;
    padding: 24px;
    border: 1.5px dashed rgba(108, 95, 252, 0.35);
    border-radius: 14px;
    background: rgba(108, 95, 252, 0.04);
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;

    &:hover,
    &--active {
      border-color: $primary;
      background: rgba(108, 95, 252, 0.08);
    }

    &--disabled {
      pointer-events: none;
      opacity: 0.65;
    }
  }

  &__drop-title {
    margin-top: 8px;
    font-weight: 600;
    color: #282f53;
  }

  &__drop-caption {
    font-size: 13px;
    color: #777a8f;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 280px;
    margin-top: 16px;
    overflow: auto;
  }

  &__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px;
    border-radius: 12px;
    background: #f6f6fa;
  }

  &__preview {
    flex: 0 0 56px;
    width: 56px;
    height: 56px;
    overflow: hidden;
    border-radius: 8px;
    background: #e4e4ee;

    img,
    video {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }

  &__info {
    min-width: 0;
    flex: 1;
  }

  &__name {
    font-size: 13px;
    font-weight: 600;
    color: #282f53;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__meta {
    font-size: 12px;
    color: #777a8f;
  }

  &__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 16px;
  }
}

.hidden {
  display: none;
}
</style>
