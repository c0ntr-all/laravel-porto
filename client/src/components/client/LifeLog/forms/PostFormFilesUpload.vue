<template>
  <div class="post-form-files-upload">
    <AppMediaAttach
      :limit="pickerLimit"
      :accept="POST_FILE_ACCEPT"
      :exclude-ids="blockedIds"
      @select-files="appendFiles"
      @select-gallery="appendGallery"
    />

    <div class="text-caption text-grey-7 q-mt-xs">
      С устройства или из альбомов галереи. Фото, видео, PDF, Office, архивы
    </div>

    <q-banner
      v-if="unsupportedFiles.length"
      class="bg-orange-1 text-orange-10 q-mt-sm"
      rounded
      dense
    >
      Неподдерживаемые файлы: {{ unsupportedNames }}
    </q-banner>

    <div v-if="items?.length" class="post-form-files-upload__grid q-mt-sm">
      <template v-for="entry in items" :key="entry.key">
        <PostFormAttachmentPreview
          v-if="entry.source === 'device'"
          :file="entry.file"
          removable
          @toggle-remove="removeItem(entry.key)"
        />
        <PostFormAttachmentPreview
          v-else
          :media="entry.media"
          removable
          @toggle-remove="removeItem(entry.key)"
        />
      </template>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useQuasar } from 'quasar'
import { POST_FILE_ACCEPT, POST_GALLERY_ATTACH_LIMIT } from 'src/constants/LifeLog/attachment'
import { groupFileTypes } from 'src/services/post.service'
import { IGalleryMediaItem, IPostFormPendingAttachment } from 'src/types'
import AppMediaAttach from 'src/components/default/AppMediaAttach.vue'
import PostFormAttachmentPreview from 'src/components/client/LifeLog/forms/PostFormAttachmentPreview.vue'

const props = withDefaults(defineProps<{
  excludeIds?: string[]
  galleryLimit?: number
}>(), {
  excludeIds: () => [],
  galleryLimit: POST_GALLERY_ATTACH_LIMIT
})

const items = defineModel<IPostFormPendingAttachment[]>({
  default: () => []
})

const $q = useQuasar()
const unsupportedFiles = ref<File[]>([])
const fileSeq = ref(0)

const unsupportedNames = computed(() =>
  unsupportedFiles.value.map(file => file.name).join(', ')
)

const pickerLimit = computed(() => {
  const used = (items.value ?? []).filter(entry => entry.source === 'gallery').length

  return Math.max(0, props.galleryLimit - used)
})

const blockedIds = computed(() => {
  const ids = [...props.excludeIds]

  for (const entry of items.value ?? []) {
    if (entry.source === 'gallery') {
      ids.push(entry.media.id)
    }
  }

  return ids
})

function nextFileKey(file: File): string {
  fileSeq.value += 1

  return `file-${fileSeq.value}-${file.name}-${file.size}-${file.lastModified}`
}

function appendFiles(files: File[]) {
  const groups = groupFileTypes(files)
  unsupportedFiles.value = groups.other
  const accepted = [...groups.images, ...groups.videos, ...groups.documents]

  if (accepted.length) {
    items.value = [
      ...(items.value ?? []),
      ...accepted.map(file => ({
        key: nextFileKey(file),
        source: 'device' as const,
        file
      }))
    ]
  }

  if (groups.other.length) {
    $q.notify({
      type: 'warning',
      message: `Некоторые файлы не поддерживаются: ${groups.other.map(file => file.name).join(', ')}`
    })
  }
}

function appendGallery(media: IGalleryMediaItem[]) {
  const next = [...(items.value ?? [])]
  const known = new Set<string>(props.excludeIds)

  for (const entry of next) {
    if (entry.source === 'gallery') {
      known.add(entry.media.id)
    }
  }

  for (const item of media) {
    if (known.has(item.id)) {
      continue
    }

    const galleryCount = next.filter(entry => entry.source === 'gallery').length

    if (galleryCount >= props.galleryLimit) {
      break
    }

    known.add(item.id)
    next.push({
      key: `gallery-${item.id}`,
      source: 'gallery',
      media: item
    })
  }

  items.value = next
}

function removeItem(key: string) {
  items.value = (items.value ?? []).filter(item => item.key !== key)
}
</script>

<style lang="scss" scoped>
.post-form-files-upload {
  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
    gap: 8px;
  }
}
</style>
