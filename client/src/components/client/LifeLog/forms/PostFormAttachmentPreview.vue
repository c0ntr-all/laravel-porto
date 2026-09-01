<template>
  <div class="post-form-attachment-preview" :class="{ 'post-form-attachment-preview--deleted': deleted }">
    <button
      v-if="removable"
      type="button"
      class="post-form-attachment-preview__remove"
      @click="emit('toggle-remove')"
    >
      <q-icon
        :name="deleted ? 'undo' : 'close'"
        size="16px"
        :color="deleted ? 'primary' : 'grey-7'"
      />
    </button>

    <div v-if="isDocument" class="post-form-attachment-preview__document">
      <q-icon :name="documentIcon" size="32px" color="primary" />
      <div class="post-form-attachment-preview__document-name ellipsis">
        {{ documentName }}
      </div>
      <div class="text-caption text-grey-7">
        {{ documentMeta }}
      </div>
    </div>

    <img
      v-else-if="attachmentPreviewSrc"
      :src="attachmentPreviewSrc"
      :alt="documentName"
      class="post-form-attachment-preview__image"
    >

    <div v-else class="post-form-attachment-preview__fallback">
      <q-icon name="insert_drive_file" size="28px" color="grey-6" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onUnmounted, ref, watch } from 'vue'
import { IPostAttachment } from 'src/types'
import { isGalleryVideo, resolveMediaUrl } from 'src/utils/gallery'
import {
  formatFileSize,
  getDocumentIconName,
  getFileExtension,
  isDocumentFile,
  isPostDocumentAttachment
} from 'src/utils/document'

const props = defineProps<{
  attachment?: IPostAttachment
  file?: File
  deleted?: boolean
  removable?: boolean
}>()

const emit = defineEmits<{
  'toggle-remove': []
}>()

const isDocument = computed(() => {
  if (props.file) {
    return isDocumentFile(props.file)
  }

  return props.attachment ? isPostDocumentAttachment(props.attachment) : false
})

const documentName = computed(() => {
  if (props.file) {
    return props.file.name
  }

  if (props.attachment && isPostDocumentAttachment(props.attachment)) {
    return props.attachment.original_name
  }

  return 'Файл'
})

const documentMeta = computed(() => {
  if (props.file) {
    return formatFileSize(props.file.size)
  }

  if (props.attachment && isPostDocumentAttachment(props.attachment)) {
    return `${props.attachment.extension.toUpperCase()} · ${formatFileSize(props.attachment.size)}`
  }

  return ''
})

const documentIcon = computed(() => {
  if (props.file) {
    return getDocumentIconName(getFileExtension(props.file.name))
  }

  if (props.attachment && isPostDocumentAttachment(props.attachment)) {
    return getDocumentIconName(props.attachment.extension)
  }

  return 'insert_drive_file'
})

const previewSrc = ref('')

watch(
  () => props.file,
  file => {
    if (previewSrc.value) {
      URL.revokeObjectURL(previewSrc.value)
      previewSrc.value = ''
    }

    if (file && (file.type.startsWith('image/') || file.type.startsWith('video/'))) {
      previewSrc.value = URL.createObjectURL(file)
    }
  },
  { immediate: true }
)

const attachmentPreviewSrc = computed(() => {
  if (props.file) {
    return previewSrc.value
  }

  if (!props.attachment || isPostDocumentAttachment(props.attachment)) {
    return ''
  }

  if (isGalleryVideo(props.attachment)) {
    return resolveMediaUrl(props.attachment.list_thumb_path)
  }

  return resolveMediaUrl(props.attachment.list_thumb_path || props.attachment.preview_thumb_path)
})

onUnmounted(() => {
  if (previewSrc.value) {
    URL.revokeObjectURL(previewSrc.value)
  }
})
</script>

<style scoped lang="scss">
.post-form-attachment-preview {
  position: relative;
  width: 112px;
  min-height: 112px;
  border: 1px solid #e4e7eb;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;

  &--deleted {
    opacity: 0.55;
    filter: grayscale(0.4);
  }

  &__remove {
    position: absolute;
    top: 4px;
    right: 4px;
    z-index: 2;
    border: 0;
    border-radius: 50%;
    background: #fff;
    padding: 2px;
    cursor: pointer;
  }

  &__image {
    width: 100%;
    height: 112px;
    object-fit: cover;
    display: block;
  }

  &__document,
  &__fallback {
    min-height: 112px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px;
    text-align: center;
  }

  &__document-name {
    width: 100%;
    font-size: 0.75rem;
    font-weight: 500;
  }
}
</style>
