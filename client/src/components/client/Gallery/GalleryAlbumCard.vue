<template>
  <router-link
    class="album-card"
    :to="{ name: 'gallery-album', params: { id: album.id } }"
  >
    <div class="album-card__cover-wrap">
      <div class="album-card__cover">
        <q-img
          v-if="hasCover"
          :src="album.image"
          :alt="album.name"
          class="album-card__image"
          fit="cover"
        >
          <template #error>
            <div class="album-card__placeholder">
              <q-icon name="photo_library" size="42px" />
            </div>
          </template>
        </q-img>
        <div v-else class="album-card__placeholder">
          <q-icon name="photo_library" size="42px" />
        </div>

        <div v-if="album.media_count" class="album-card__count">
          <q-icon name="collections" size="14px" />
          {{ album.media_count }}
        </div>
        <div v-if="album.is_system" class="album-card__badge">System</div>
      </div>
    </div>

    <div class="album-card__meta">
      <div class="album-card__name" :title="album.name">{{ album.name }}</div>
      <div v-if="album.description" class="album-card__description" :title="album.description">
        {{ album.description }}
      </div>
      <div v-if="formattedDate" class="album-card__date">{{ formattedDate }}</div>
    </div>
  </router-link>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { date } from 'quasar'
import { IGalleryAlbum } from 'src/types/gallery'
import { hasAlbumCover } from 'src/utils/gallery'

const props = defineProps<{
  album: IGalleryAlbum
}>()

const hasCover = computed(() => hasAlbumCover(props.album.image))

const formattedDate = computed(() => {
  if (!props.album.created_at) {
    return ''
  }

  return date.formatDate(props.album.created_at, 'D MMM YYYY')
})
</script>

<style lang="scss" scoped>
.album-card {
  display: block;
  color: inherit;
  text-decoration: none;
  min-width: 0;

  &:hover {
    .album-card__cover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(40, 47, 83, 0.16);
    }

    .album-card__image {
      transform: scale(1.04);
    }

    .album-card__name {
      color: $primary;
    }
  }

  &__cover-wrap {
    position: relative;
    padding-top: 10px;
    padding-right: 10px;
    margin-bottom: 0.75rem;
  }

  &__cover-wrap::before,
  &__cover-wrap::after {
    content: '';
    position: absolute;
    inset: 0 0 10px 10px;
    border-radius: 16px;
    background: #fff;
    border: 1px solid rgba(40, 47, 83, 0.08);
  }

  &__cover-wrap::before {
    transform: translate(8px, -8px) scale(0.94);
    opacity: 0.45;
  }

  &__cover-wrap::after {
    transform: translate(4px, -4px) scale(0.97);
    opacity: 0.7;
  }

  &__cover {
    position: relative;
    z-index: 1;
    overflow: hidden;
    aspect-ratio: 4 / 3;
    border-radius: 16px;
    background: #ececf4;
    box-shadow: 0 6px 16px rgba(40, 47, 83, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  &__image {
    height: 100%;
    transition: transform 0.35s ease;

    :deep(.q-img__image) {
      transition: transform 0.35s ease;
    }
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
    background:
      linear-gradient(135deg, rgba(108, 95, 252, 0.12), rgba(38, 166, 154, 0.08));
  }

  &__count {
    position: absolute;
    top: 10px;
    right: 10px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(18, 18, 18, 0.72);
    color: #fff;
    font-size: 12px;
    line-height: 1;
    backdrop-filter: blur(6px);
  }

  &__badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(108, 95, 252, 0.9);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.02em;
    backdrop-filter: blur(6px);
  }

  &__name {
    margin-bottom: 2px;
    font-size: 15px;
    font-weight: 600;
    color: #282f53;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.15s ease;
  }

  &__description,
  &__date {
    font-size: 13px;
    line-height: 1.35;
    color: #777a8f;
  }

  &__description {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    overflow: hidden;
    margin-bottom: 2px;
  }
}
</style>
