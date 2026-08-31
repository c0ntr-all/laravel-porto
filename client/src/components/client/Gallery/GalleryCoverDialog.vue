<template>
  <q-dialog v-model="show">
    <q-card class="cover-dialog">
      <q-card-section class="cover-dialog__header">
        <div>
          <div class="cover-dialog__title">Change cover</div>
          <div class="cover-dialog__subtitle">Choose a photo from the album or upload one from this device</div>
        </div>
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-btn-toggle
          v-model="tab"
          class="cover-dialog__toggle"
          toggle-color="primary"
          unelevated
          no-caps
          spread
          :options="tabOptions"
        />
      </q-card-section>

      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <q-tab-panel name="album" class="q-pa-md">
          <div v-if="photos.length" class="cover-dialog__grid">
            <GalleryMediaCard
              v-for="item in photos"
              :key="item.id"
              :media="item"
              :selected="item.id === selectedId"
              @click="selectExisting(item)"
            />
          </div>
          <div v-else class="cover-dialog__empty">
            There are no photos in this album yet. Upload one from the device tab.
          </div>
        </q-tab-panel>

        <q-tab-panel name="device" class="q-pa-md">
          <div
            class="cover-dialog__dropzone"
            :class="{
              'cover-dialog__dropzone--active': isDragging,
              'cover-dialog__dropzone--disabled': busy
            }"
            @click="openFilePicker"
            @dragenter.prevent="isDragging = true"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
          >
            <q-icon name="add_photo_alternate" size="32px" color="primary" />
            <div class="cover-dialog__drop-title">Drop a photo here</div>
            <div class="cover-dialog__drop-caption">or click to browse</div>
          </div>

          <input
            ref="fileInput"
            class="hidden"
            type="file"
            :accept="GALLERY_IMAGE_ACCEPT"
            @change="onFileInput"
          >

          <div v-if="previewUrl" class="cover-dialog__preview">
            <img :src="previewUrl" alt="Cover preview">
          </div>

          <div class="cover-dialog__actions">
            <q-btn
              color="primary"
              unelevated
              no-caps
              label="Set as cover"
              :disable="!file"
              :loading="busy"
              @click="uploadCover"
            />
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, onUnmounted, ref, watch } from 'vue'
import { Notify } from 'quasar'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { IGalleryMediaItem } from 'src/types/gallery'
import { GALLERY_IMAGE_ACCEPT, isGalleryVideo, isVideoFile } from 'src/utils/gallery'
import GalleryMediaCard from 'src/components/client/Gallery/GalleryMediaCard.vue'

const show = defineModel<boolean>({ default: false })
const galleryStore = useGalleryStore()
const tab = ref<'album' | 'device'>('album')
const selectedId = ref('')
const file = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const isDragging = ref(false)
const objectUrl = ref('')

const tabOptions = [
  { label: 'From album', value: 'album', icon: 'collections' },
  { label: 'From device', value: 'device', icon: 'upload_file' }
]

const photos = computed(() => (
  galleryStore.album?.media.filter(item => !isGalleryVideo(item)) ?? []
))

const busy = computed(() => galleryStore.isSaving || galleryStore.isUploading)
const previewUrl = computed(() => objectUrl.value)

watch(show, (visible) => {
  if (visible) {
    tab.value = photos.value.length ? 'album' : 'device'
    selectedId.value = ''
    clearFile()
  }
})

function openFilePicker(): void {
  if (!busy.value) {
    fileInput.value?.click()
  }
}

function onFileInput(event: Event): void {
  const input = event.target as HTMLInputElement
  setFile(input.files?.[0] ?? null)
  input.value = ''
}

function onDrop(event: DragEvent): void {
  isDragging.value = false
  setFile(event.dataTransfer?.files?.[0] ?? null)
}

function setFile(next: File | null): void {
  if (next && isVideoFile(next)) {
    Notify.create({
      type: 'warning',
      message: 'Cover must be a photo'
    })
    return
  }

  clearFile()
  file.value = next

  if (next) {
    objectUrl.value = URL.createObjectURL(next)
  }
}

function clearFile(): void {
  if (objectUrl.value) {
    URL.revokeObjectURL(objectUrl.value)
    objectUrl.value = ''
  }

  file.value = null
}

async function selectExisting(item: IGalleryMediaItem): Promise<void> {
  if (busy.value) {
    return
  }

  selectedId.value = item.id

  try {
    await galleryStore.updateCoverFromMedia(item)
    show.value = false
  } catch {
    selectedId.value = ''
  }
}

async function uploadCover(): Promise<void> {
  if (!file.value) {
    return
  }

  try {
    await galleryStore.updateCoverFromFile(file.value)
    show.value = false
  } catch {
    // Notification is handled in the store
  }
}

onUnmounted(() => {
  clearFile()
})
</script>

<style lang="scss" scoped>
.cover-dialog {
  width: 640px;
  max-width: 92vw;
  border-radius: 16px;

  &__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  &__title {
    font-size: 20px;
    font-weight: 600;
    color: #282f53;
  }

  &__subtitle {
    margin-top: 2px;
    font-size: 13px;
    color: #777a8f;
  }

  &__toggle {
    width: 100%;
    border-radius: 12px;
    background: #f0f0f5;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
    gap: 8px;
    max-height: 360px;
    overflow: auto;
  }

  &__empty {
    padding: 32px 8px;
    color: #9aa0b8;
    font-size: 13px;
    text-align: center;
  }

  &__dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-height: 140px;
    padding: 20px;
    border: 1.5px dashed rgba(108, 95, 252, 0.35);
    border-radius: 14px;
    background: rgba(108, 95, 252, 0.04);
    cursor: pointer;

    &:hover,
    &--active {
      border-color: $primary;
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

  &__drop-caption,
  &__drop-title + &__drop-caption {
    font-size: 13px;
    color: #777a8f;
  }

  &__preview {
    margin-top: 12px;
    overflow: hidden;
    border-radius: 12px;
    max-height: 180px;
    background: #111;

    img {
      display: block;
      width: 100%;
      max-height: 180px;
      object-fit: contain;
    }
  }

  &__actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
  }
}

.hidden {
  display: none;
}
</style>
