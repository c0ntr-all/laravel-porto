<template>
  <div class="upload-path">
    <p class="upload-path__hint">
      Files are not copied. Only the path is sent — later the backend will serve the object from this computer.
    </p>

    <q-input
      v-model="folderPath"
      label="Folder path"
      hint="Example: D:\Photos\Vacation"
      outlined
      dense
      class="q-mb-md"
    >
      <template #prepend>
        <q-icon name="folder" />
      </template>
    </q-input>

    <div class="upload-path__pickers">
      <q-file
        v-model="pickedFiles"
        label="Pick files to capture names"
        outlined
        dense
        multiple
        clearable
        :accept="GALLERY_MEDIA_ACCEPT"
        @update:model-value="onFilesPicked"
      >
        <template #prepend>
          <q-icon name="photo_library" />
        </template>
      </q-file>
    </div>

    <div class="upload-path__manual">
      <q-input
        v-model="manualPath"
        label="Or paste a full file path"
        outlined
        dense
        @keyup.enter="addManualPath"
      >
        <template #append>
          <q-btn
            icon="add"
            flat
            round
            dense
            :disable="!manualPath.trim()"
            @click="addManualPath"
          />
        </template>
      </q-input>
    </div>

    <div v-if="paths.length" class="upload-path__list">
      <div
        v-for="item in paths"
        :key="item.path"
        class="upload-path__item"
        :class="{ 'upload-path__item--finished': item.status === 'finished' }"
      >
        <q-icon
          :name="resolveMediaKind(item.path) === 'video' ? 'movie' : 'image'"
          color="primary"
        />
        <div class="upload-path__item-body">
          <div class="upload-path__item-name">{{ fileName(item.path) }}</div>
          <div class="upload-path__item-path" :title="item.path">{{ item.path }}</div>
          <div v-if="item.status === 'finished'" class="upload-path__status">Finished</div>
          <div v-else-if="item.status === 'error'" class="text-negative text-caption">
            {{ item.error || 'Upload failed' }}
          </div>
        </div>
        <q-chip
          size="sm"
          dense
          :color="resolveMediaKind(item.path) === 'video' ? 'deep-purple-1' : 'grey-3'"
          text-color="dark"
        >
          {{ resolveMediaKind(item.path) === 'video' ? 'Video' : 'Photo' }}
        </q-chip>
        <q-btn
          v-if="item.status === 'finished'"
          icon="check"
          flat
          round
          dense
          color="positive"
          disable
        />
        <q-btn
          v-else
          icon="close"
          flat
          round
          dense
          :disable="galleryStore.isUploading"
          @click="removePath(item.path)"
        />
      </div>
    </div>

    <div v-else class="upload-path__empty">
      No paths yet. Pick files from a folder or paste a full path.
    </div>

    <div class="upload-path__actions">
      <q-btn
        flat
        no-caps
        label="Clear"
        :disable="!paths.length || galleryStore.isUploading"
        @click="clearAll"
      />
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="input"
        :label="submitLabel"
        :loading="galleryStore.isUploading"
        :disable="!pendingCount"
        @click="submit"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { Notify } from 'quasar'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { GALLERY_MEDIA_ACCEPT, joinLocalPath, resolveMediaKind } from 'src/utils/gallery'

interface IUploadPathItem {
  path: string
  status: 'pending' | 'uploading' | 'finished' | 'error'
  error?: string
}

const galleryStore = useGalleryStore()
const folderPath = ref('')
const pickedFiles = ref<File[] | null>(null)
const manualPath = ref('')
const paths = ref<IUploadPathItem[]>([])

const pendingCount = computed(() => (
  paths.value.filter(item => item.status === 'pending' || item.status === 'error').length
))

const submitLabel = computed(() => {
  if (!pendingCount.value) {
    return 'Send paths'
  }

  return `Send ${pendingCount.value} ${pendingCount.value === 1 ? 'path' : 'paths'}`
})

function addPath(path: string): void {
  const value = path.trim()

  if (!value || paths.value.some(item => item.path === value)) {
    return
  }

  paths.value.push({
    path: value,
    status: 'pending'
  })
}

function onFilesPicked(files: File[] | null): void {
  if (!files?.length) {
    return
  }

  const folder = folderPath.value.trim()

  if (!folder) {
    Notify.create({
      type: 'warning',
      message: 'Enter the folder path first, then pick files to capture names.'
    })
    pickedFiles.value = null
    return
  }

  for (const file of files) {
    addPath(joinLocalPath(folder, file.name))
  }
}

function addManualPath(): void {
  addPath(manualPath.value)
  manualPath.value = ''
}

function removePath(path: string): void {
  paths.value = paths.value.filter(item => item.path !== path)
}

function clearAll(): void {
  paths.value = []
  pickedFiles.value = null
  manualPath.value = ''
}

function fileName(path: string): string {
  return path.split(/[\\/]/).pop() || path
}

async function submit(): Promise<void> {
  const queue = paths.value.filter(item => item.status === 'pending' || item.status === 'error')

  if (!queue.length) {
    return
  }

  for (const item of queue) {
    item.status = 'uploading'
    item.error = undefined
  }

  try {
    await galleryStore.uploadFromWindows(queue.map(item => item.path))

    for (const item of queue) {
      item.status = 'finished'
    }
  } catch {
    for (const item of queue) {
      item.status = 'error'
      item.error = 'Upload failed'
    }
  }
}
</script>

<style lang="scss" scoped>
.upload-path {
  padding: 16px 20px 20px;

  &__hint {
    margin: 0 0 16px;
    font-size: 13px;
    line-height: 1.45;
    color: #777a8f;
  }

  &__pickers,
  &__manual {
    margin-bottom: 12px;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 220px;
    overflow: auto;
  }

  &__item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 12px;
    background: #f6f6fa;
  }

  &__item-body {
    min-width: 0;
    flex: 1;
  }

  &__item-name {
    font-size: 13px;
    font-weight: 600;
    color: #282f53;
  }

  &__item-path {
    font-size: 12px;
    color: #777a8f;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__status {
    margin-top: 2px;
    font-size: 12px;
    font-weight: 600;
    color: #21ba45;
  }

  &__item--finished {
    background: #eefaf1;
  }

  &__empty {
    padding: 18px 8px;
    color: #9aa0b8;
    font-size: 13px;
    text-align: center;
  }

  &__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 16px;
  }
}
</style>
