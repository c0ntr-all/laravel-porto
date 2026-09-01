<template>
  <div class="viewer-actions">
    <q-btn
      flat
      no-caps
      dense
      :icon="saved ? 'bookmark' : 'bookmark_border'"
      :label="saved ? 'Сохранено' : 'Сохранить себе'"
      :disable="saved || galleryStore.isSavingMedia || !item"
      :loading="galleryStore.isSavingMedia"
      @click="save"
    />
    <q-btn
      flat
      no-caps
      dense
      icon="open_in_new"
      label="Открыть оригинал"
      :disable="!originalUrl"
      :href="originalUrl || undefined"
      target="_blank"
      rel="noopener noreferrer"
    />
  </div>
</template>

<script lang="ts" setup>
import { computed, watch } from 'vue'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { IGalleryMediaItem } from 'src/types/gallery'
import { getMediaOriginalUrl, isGalleryVideo } from 'src/utils/gallery'
import { IImageSource } from 'src/types/carousel'

const props = defineProps<{
  item?: IImageSource | IGalleryMediaItem | null
}>()

const galleryStore = useGalleryStore()

const saved = computed(() => {
  if (!props.item) {
    return false
  }

  return galleryStore.isMediaSaved({
    id: String(props.item.id),
    album_id: 'album_id' in props.item ? props.item.album_id : undefined,
    saved_from_id: props.item.saved_from_id
  })
})

const originalUrl = computed(() => (
  props.item ? getMediaOriginalUrl(props.item) : ''
))

watch(() => props.item?.id, () => {
  void galleryStore.ensureSaveAlbum()
}, { immediate: true })

async function save(): Promise<void> {
  if (!props.item || saved.value) {
    return
  }

  await galleryStore.saveMediaToSaveAlbum({
    id: String(props.item.id),
    type: isGalleryVideo(props.item) ? 'video' : 'photo',
    name: '',
    description: props.item.description ?? null,
    original_path: props.item.original_path,
    original: props.item.original || props.item.original_path,
    list_thumb_path: props.item.list_thumb_path ?? '',
    preview_thumb_path: props.item.preview_thumb_path,
    attachment_type: props.item.attachment_type,
    width: props.item.width,
    height: props.item.height,
    duration: null,
    album_id: 'album_id' in props.item ? (props.item.album_id ?? null) : null,
    saved_from_id: props.item.saved_from_id ?? null
  })
}
</script>

<style lang="scss" scoped>
.viewer-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #1a1a1a;
  color: #fff;

  :deep(.q-btn) {
    color: #fff;
  }

  :deep(.q-btn.disabled) {
    opacity: 0.55;
  }
}
</style>
