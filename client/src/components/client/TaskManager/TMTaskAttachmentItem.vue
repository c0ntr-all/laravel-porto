<template>
  <div class="task-attachment">
    <button
      v-if="!isDocument"
      type="button"
      class="task-attachment__media"
      @click="$emit('preview')"
    >
      <q-img
        :src="thumbSrc"
        class="task-attachment__thumb"
        fit="cover"
      >
        <template #error>
          <div class="absolute-full flex flex-center bg-grey-3 text-grey-7">
            <q-icon :name="isVideo ? 'videocam' : 'broken_image'" size="28px" />
          </div>
        </template>
      </q-img>
      <span
        v-if="isVideo"
        class="task-attachment__play"
      >
        <q-icon name="play_circle" size="32px" color="white" />
      </span>
    </button>

    <a
      v-else
      :href="downloadHref"
      class="task-attachment__document"
      target="_blank"
      rel="noopener noreferrer"
      @click.stop
    >
      <q-icon
        :name="documentIcon"
        size="26px"
        color="primary"
      />
      <div class="task-attachment__document-meta">
        <div class="task-attachment__name ellipsis">{{ documentName }}</div>
        <div class="task-attachment__details">{{ documentDetails }}</div>
      </div>
      <q-icon name="download" size="18px" color="grey-6" />
    </a>

    <q-btn
      class="task-attachment__remove"
      icon="close"
      size="sm"
      round
      dense
      flat
      color="grey-8"
      @click.stop="$emit('remove')"
    >
      <q-tooltip>Открепить</q-tooltip>
    </q-btn>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IPostAttachment, IPostDocumentAttachment } from 'src/types'
import { formatFileSize, getDocumentIconName, isPostDocumentAttachment } from 'src/utils/document'
import { getAttachmentThumbSrc, isGalleryVideo, resolveMediaUrl } from 'src/utils/gallery'

const props = defineProps<{
  attachment: IPostAttachment
}>()

defineEmits<{
  (e: 'preview'): void
  (e: 'remove'): void
}>()

const isDocument = computed(() => isPostDocumentAttachment(props.attachment))
const isVideo = computed(() => isGalleryVideo(props.attachment))
const thumbSrc = computed(() => {
  if (isPostDocumentAttachment(props.attachment)) {
    return ''
  }

  return getAttachmentThumbSrc(props.attachment)
})

const documentAttachment = computed(() =>
  isDocument.value ? props.attachment as IPostDocumentAttachment : null
)
const documentName = computed(() => documentAttachment.value?.original_name ?? '')
const documentIcon = computed(() => getDocumentIconName(documentAttachment.value?.extension ?? ''))
const documentDetails = computed(() => {
  if (!documentAttachment.value) return ''
  return `${documentAttachment.value.extension.toUpperCase()} · ${formatFileSize(documentAttachment.value.size)}`
})
const downloadHref = computed(() =>
  resolveMediaUrl(documentAttachment.value?.download_url)
)
</script>

<style scoped lang="scss">
.task-attachment {
  position: relative;

  &:hover &__remove {
    opacity: 1;
  }

  &__media {
    position: relative;
    display: block;
    width: 100%;
    padding: 0;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    border: 0;
    border-radius: 10px;
    background: #eef0f5;
    cursor: pointer;
  }

  &__thumb {
    width: 100%;
    height: 100%;
  }

  &__play {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(17, 24, 39, 0.28);
  }

  &__document {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 56px;
    padding: 10px 36px 10px 12px;
    border: 1px solid #e4e6ee;
    border-radius: 10px;
    background: #f8fafc;
    color: inherit;
    text-decoration: none;

    &:hover {
      background: #f1f5f9;
    }
  }

  &__document-meta {
    min-width: 0;
    flex: 1;
  }

  &__name {
    font-size: 14px;
    font-weight: 600;
    color: #1f2439;
  }

  &__details {
    margin-top: 2px;
    color: #6b7280;
    font-size: 12px;
  }

  &__remove {
    position: absolute;
    top: 4px;
    right: 4px;
    opacity: 0;
    background: rgba(255, 255, 255, 0.92);
    transition: opacity 0.15s ease;
  }

  &:hover &__remove,
  &__remove:focus {
    opacity: 1;
  }
}
</style>
