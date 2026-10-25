<template>
  <div class="ll-album-subject">
    <img
      v-if="coverUrl"
      :src="coverUrl"
      alt=""
      class="ll-album-subject__cover"
      draggable="false"
    >
    <div
      v-else
      class="ll-album-subject__cover ll-album-subject__cover--empty"
    >
      <q-icon name="photo_album" size="28px" />
    </div>

    <div class="ll-album-subject__body">
      <div class="ll-album-subject__title">
        {{ album.name }}
      </div>
      <div
        v-if="album.description"
        class="ll-album-subject__desc"
      >
        {{ album.description }}
      </div>
      <div
        v-if="album.media_count != null"
        class="ll-album-subject__meta"
      >
        <q-icon name="collections" size="14px" />
        {{ album.media_count }}
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IPostAlbumSubject } from 'src/types'
import { hasAlbumCover, resolveMediaUrl } from 'src/utils/gallery'

const props = defineProps<{
  album: IPostAlbumSubject
}>()

const coverUrl = computed(() => {
  if (!hasAlbumCover(props.album.image)) {
    return ''
  }

  return resolveMediaUrl(props.album.image)
})
</script>

<style scoped lang="scss">
.ll-album-subject {
  display: flex;
  gap: 12px;
  padding: 10px;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  background: linear-gradient(135deg, #f4fbff 0%, #ffffff 55%);

  &__cover {
    width: 84px;
    height: 84px;
    flex: none;
    border-radius: 10px;
    object-fit: cover;
    background: #ececf4;

    &--empty {
      display: flex;
      align-items: center;
      justify-content: center;
      color: #9aa0b8;
    }
  }

  &__body {
    min-width: 0;
    flex: 1;
    padding: 2px 2px 2px 0;
  }

  &__title {
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.35;
    color: #1f2937;
  }

  &__desc {
    margin-top: 4px;
    color: #4b5563;
    font-size: 13px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  &__meta {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 6px;
    color: #6b7280;
    font-size: 12px;
  }
}
</style>
