<template>
  <a
    :href="downloadHref"
    class="ll-post-document"
    target="_blank"
    rel="noopener noreferrer"
    @click.stop
  >
    <q-icon
      :name="iconName"
      size="28px"
      color="primary"
      class="ll-post-document__icon"
    />
    <div class="ll-post-document__meta">
      <div class="ll-post-document__name ellipsis">
        {{ document.original_name }}
      </div>
      <div class="ll-post-document__details text-caption text-grey-7">
        {{ document.extension.toUpperCase() }} · {{ fileSize }}
      </div>
    </div>
    <q-icon name="download" size="18px" color="grey-6" />
  </a>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IPostDocumentAttachment } from 'src/types'
import { resolveMediaUrl } from 'src/utils/gallery'
import { formatFileSize, getDocumentIconName } from 'src/utils/document'

const props = defineProps<{
  document: IPostDocumentAttachment
}>()

const iconName = computed(() => getDocumentIconName(props.document.extension))
const fileSize = computed(() => formatFileSize(props.document.size))
const downloadHref = computed(() => resolveMediaUrl(props.document.download_url))
</script>

<style scoped lang="scss">
.ll-post-document {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: 1px solid #e4e7eb;
  border-radius: 10px;
  background: #f8fafc;
  color: inherit;
  text-decoration: none;
  transition: background-color 0.15s ease;

  &:hover {
    background: #f1f5f9;
  }

  &__meta {
    min-width: 0;
    flex: 1;
  }

  &__name {
    font-size: 0.92rem;
    font-weight: 500;
  }
}
</style>
