<template>
  <q-dialog
    v-model="show"
    persistent
  >
    <q-card class="gallery-picker">
      <header class="gallery-picker__header">
        <div class="gallery-picker__side">
          <q-btn
            v-if="openedAlbum"
            flat
            dense
            no-caps
            color="dark"
            icon="arrow_back"
            label="Назад"
            aria-label="Назад"
            @click="back"
          />
        </div>
        <div class="gallery-picker__title ellipsis">
          {{ title }}
        </div>
        <div class="gallery-picker__side gallery-picker__side--end">
          <q-btn
            flat
            round
            dense
            icon="close"
            aria-label="Закрыть"
            @click="cancel"
          />
        </div>
      </header>

      <q-separator />

      <div class="gallery-picker__body">
        <template v-if="openedAlbum">
          <div v-if="isAlbumLoading && !activeMedia.length" class="gallery-picker__state">
            <q-spinner-dots color="primary" size="36px" />
          </div>

          <div v-else-if="albumError" class="gallery-picker__state">
            <q-icon name="cloud_off" size="36px" />
            <div>{{ albumError }}</div>
            <q-btn
              flat
              no-caps
              color="primary"
              label="Повторить"
              @click="retryAlbum"
            />
          </div>

          <div v-else-if="!activeMedia.length" class="gallery-picker__state">
            <q-icon name="photo_library" size="36px" />
            <div>В этом альбоме нет фото и видео</div>
          </div>

          <div v-else class="gallery-picker__grid">
            <button
              v-for="item in activeMedia"
              :key="item.id"
              type="button"
              class="tile"
              :class="{
                'tile--selected': isSelected(item.id),
                'tile--locked': isLocked(item.id)
              }"
              :disabled="isLocked(item.id)"
              :aria-pressed="isSelected(item.id)"
              :aria-label="itemLabel(item)"
              @click="toggle(item)"
            >
              <img
                v-if="thumb(item) && !failedThumbs.has(item.id)"
                :src="thumb(item)"
                alt=""
                draggable="false"
                loading="lazy"
                decoding="async"
                @error="onThumbError(item.id)"
              >
              <span v-else class="tile__fallback">
                <q-icon :name="isGalleryVideo(item) ? 'movie' : 'image'" size="28px" />
              </span>

              <span
                v-if="isExcluded(item.id)"
                class="tile__mark tile__mark--taken"
              >
                <q-icon name="check" size="14px" />
              </span>
              <span
                v-else-if="isSelected(item.id) || !limitReached"
                class="tile__mark"
                :class="{ 'tile__mark--on': isSelected(item.id) }"
              >
                <q-icon v-if="isSelected(item.id)" name="check" size="14px" />
              </span>

              <span v-if="isGalleryVideo(item)" class="tile__video">
                <q-icon name="play_arrow" size="14px" />
                <span v-if="durationLabel(item)">{{ durationLabel(item) }}</span>
              </span>
            </button>
          </div>
        </template>

        <template v-else>
          <div v-if="isAlbumsLoading && !albums.length" class="gallery-picker__skeletons">
            <div v-for="index in 5" :key="index" class="album-row album-row--skeleton">
              <q-skeleton type="rect" width="56px" height="56px" />
              <span class="album-row__text">
                <q-skeleton type="text" width="46%" />
                <q-skeleton type="text" width="22%" />
              </span>
            </div>
          </div>

          <div v-else-if="albumsError" class="gallery-picker__state">
            <q-icon name="cloud_off" size="36px" />
            <div>{{ albumsError }}</div>
            <q-btn
              flat
              no-caps
              color="primary"
              label="Повторить"
              @click="loadAlbums"
            />
          </div>

          <div v-else-if="!albums.length" class="gallery-picker__state">
            <q-icon name="photo_album" size="36px" />
            <div>Альбомов пока нет</div>
          </div>

          <div v-else class="gallery-picker__albums">
            <button
              v-for="album in albums"
              :key="album.id"
              type="button"
              class="album-row"
              @click="openAlbum(album)"
            >
              <img
                v-if="cover(album)"
                :src="cover(album)"
                alt=""
                class="album-row__cover"
                draggable="false"
              >
              <span v-else class="album-row__cover album-row__cover--empty">
                <q-icon name="photo_library" size="22px" />
              </span>
              <span class="album-row__text">
                <span class="album-row__name ellipsis">{{ album.name }}</span>
                <span class="album-row__meta">
                  <q-icon name="collections" size="14px" />
                  {{ album.media_count }}
                </span>
              </span>
              <span v-if="pickedByAlbum[album.id]" class="album-row__badge">
                {{ pickedByAlbum[album.id] }}
              </span>
              <q-icon name="chevron_right" color="grey-5" size="22px" />
            </button>
          </div>
        </template>
      </div>

      <q-separator />

      <footer class="gallery-picker__footer">
        <div
          class="gallery-picker__count"
          :class="{ 'gallery-picker__count--full': limitReached && limit > 0 }"
          aria-live="polite"
        >
          Выбрано {{ selectedCount }} фото из {{ limit }}
        </div>
        <div class="gallery-picker__actions">
          <q-btn
            flat
            no-caps
            color="grey-8"
            label="Отмена"
            @click="cancel"
          />
          <q-btn
            unelevated
            no-caps
            color="primary"
            label="Выбрать"
            :disable="selectedCount === 0"
            @click="confirm"
          />
        </div>
      </footer>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { galleryApi } from 'src/api/requests/galleryApi'
import { mapGalleryAlbumResponse, mapGalleryAlbumsResponse } from 'src/api/mappers/gallery.mapper'
import { IGalleryAlbum, IGalleryMediaItem } from 'src/types/gallery'
import { handleApiError } from 'src/utils/jsonapi'
import {
  formatMediaDuration,
  hasAlbumCover,
  isGalleryVideo,
  resolveMediaUrl,
  sortGalleryMedia
} from 'src/utils/gallery'

const props = defineProps<{
  limit: number
  excludeIds?: string[]
}>()

const emit = defineEmits<{
  select: [items: IGalleryMediaItem[]]
}>()

const show = defineModel<boolean>({ default: false })

const albums = ref<IGalleryAlbum[]>([])
const selected = ref<IGalleryMediaItem[]>([])
const openedAlbumId = ref<string | null>(null)
const mediaByAlbum = ref(new Map<string, IGalleryMediaItem[]>())
const failedThumbs = ref(new Set<string>())
const isAlbumsLoading = ref(false)
const isAlbumLoading = ref(false)
const albumsError = ref<string | null>(null)
const albumError = ref<string | null>(null)

let albumsRequestId = 0
let albumRequestId = 0

const excluded = computed(() => new Set(props.excludeIds ?? []))
const selectedCount = computed(() => selected.value.length)
const limitReached = computed(() => selectedCount.value >= props.limit)
const openedAlbum = computed(() =>
  albums.value.find(album => album.id === openedAlbumId.value) ?? null
)
const title = computed(() => openedAlbum.value?.name || 'Галерея')
const activeMedia = computed(() => {
  if (!openedAlbumId.value) {
    return []
  }

  return mediaByAlbum.value.get(openedAlbumId.value) ?? []
})
const pickedByAlbum = computed(() => {
  const counts: Record<string, number> = {}

  for (const item of selected.value) {
    if (!item.album_id) {
      continue
    }

    counts[item.album_id] = (counts[item.album_id] ?? 0) + 1
  }

  return counts
})

watch(show, isOpen => {
  if (!isOpen) {
    return
  }

  resetSession()
  void loadAlbums()
})

function resetSession() {
  albumRequestId += 1
  selected.value = []
  openedAlbumId.value = null
  albumsError.value = null
  albumError.value = null
  isAlbumLoading.value = false
  mediaByAlbum.value = new Map()
  failedThumbs.value = new Set()
}

async function loadAlbums() {
  const requestId = ++albumsRequestId
  isAlbumsLoading.value = true
  albumsError.value = null

  try {
    const response = await galleryApi.getAlbums()

    if (requestId !== albumsRequestId) {
      return
    }

    albums.value = mapGalleryAlbumsResponse(response)
  } catch (err) {
    if (requestId !== albumsRequestId) {
      return
    }

    albumsError.value = 'Не удалось загрузить альбомы'
    handleApiError(err)
  } finally {
    if (requestId === albumsRequestId) {
      isAlbumsLoading.value = false
    }
  }
}

async function openAlbum(album: IGalleryAlbum) {
  openedAlbumId.value = album.id
  albumError.value = null

  if (mediaByAlbum.value.has(album.id)) {
    isAlbumLoading.value = false
    return
  }

  const requestId = ++albumRequestId
  isAlbumLoading.value = true

  try {
    const response = await galleryApi.getAlbum(album.id)

    if (requestId !== albumRequestId) {
      return
    }

    const mapped = mapGalleryAlbumResponse(response)
    const next = new Map(mediaByAlbum.value)
    next.set(album.id, sortGalleryMedia(mapped.media, 'desc'))
    mediaByAlbum.value = next
  } catch (err) {
    if (requestId !== albumRequestId) {
      return
    }

    albumError.value = 'Не удалось загрузить альбом'
    handleApiError(err)
  } finally {
    if (requestId === albumRequestId) {
      isAlbumLoading.value = false
    }
  }
}

function retryAlbum() {
  const album = openedAlbum.value

  if (!album) {
    return
  }

  const next = new Map(mediaByAlbum.value)
  next.delete(album.id)
  mediaByAlbum.value = next
  void openAlbum(album)
}

function back() {
  albumRequestId += 1
  isAlbumLoading.value = false
  albumError.value = null
  openedAlbumId.value = null
}

function cancel() {
  show.value = false
}

function confirm() {
  if (!selected.value.length) {
    return
  }

  const items = selected.value.slice(0, props.limit)
  show.value = false
  emit('select', items)
}

function isExcluded(id: string): boolean {
  return excluded.value.has(id)
}

function isSelected(id: string): boolean {
  return selected.value.some(item => item.id === id)
}

function isLocked(id: string): boolean {
  if (isExcluded(id)) {
    return true
  }

  return limitReached.value && !isSelected(id)
}

function toggle(item: IGalleryMediaItem) {
  if (isExcluded(item.id)) {
    return
  }

  if (isSelected(item.id)) {
    selected.value = selected.value.filter(entry => entry.id !== item.id)
    return
  }

  if (selected.value.length >= props.limit) {
    return
  }

  selected.value = [...selected.value, item]
}

function thumb(item: IGalleryMediaItem): string {
  return resolveMediaUrl(item.list_thumb_path || item.preview_thumb_path || item.original)
}

function cover(album: IGalleryAlbum): string {
  if (!hasAlbumCover(album.image)) {
    return ''
  }

  return resolveMediaUrl(album.image)
}

function itemLabel(item: IGalleryMediaItem): string {
  const name = item.name || (isGalleryVideo(item) ? 'Видео' : 'Фото')

  if (isExcluded(item.id)) {
    return `${name}, уже добавлено`
  }

  if (isLocked(item.id)) {
    return `${name}, достигнут лимит`
  }

  return name
}

function durationLabel(item: IGalleryMediaItem): string {
  return formatMediaDuration(item.duration) ?? ''
}

function onThumbError(id: string) {
  const next = new Set(failedThumbs.value)
  next.add(id)
  failedThumbs.value = next
}
</script>

<style lang="scss" scoped>
.gallery-picker {
  display: flex;
  flex-direction: column;
  width: min(760px, 94vw);
  height: min(760px, 88vh);
  max-height: 88vh;
  border-radius: 16px;

  &__header {
    display: grid;
    grid-template-columns: 120px minmax(0, 1fr) 120px;
    align-items: center;
    gap: 8px;
    flex: none;
    padding: 8px 8px 8px 4px;
  }

  &__side--end {
    display: flex;
    justify-content: flex-end;
  }

  &__title {
    text-align: center;
    font-size: 1.05rem;
    font-weight: 600;
    color: #282f53;
  }

  &__body {
    flex: 1;
    min-height: 0;
    overflow: auto;
    padding: 12px;
  }

  &__state {
    min-height: 240px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #777a8f;
    text-align: center;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(108px, 1fr));
    gap: 4px;
  }

  &__albums,
  &__skeletons {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  &__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    flex: none;
    padding: 12px 16px;
  }

  &__count {
    color: #4b5168;
    font-size: 14px;

    &--full {
      color: #21ba45;
      font-weight: 600;
    }
  }

  &__actions {
    display: flex;
    gap: 8px;
    margin-left: auto;
  }
}

.album-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 8px;
  border: 0;
  border-radius: 12px;
  background: transparent;
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;

  &:hover {
    background: rgba(108, 95, 252, 0.06);
  }

  &:focus-visible {
    outline: 2px solid $primary;
    outline-offset: 2px;
  }

  &--skeleton {
    cursor: default;

    &:hover {
      background: transparent;
    }
  }

  &__cover {
    width: 56px;
    height: 56px;
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

  &__text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1;
  }

  &__name {
    font-weight: 600;
    color: #282f53;
  }

  &__meta {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #777a8f;
    font-size: 13px;
  }

  &__badge {
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: 999px;
    background: #21ba45;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
}

.tile {
  position: relative;
  aspect-ratio: 1;
  padding: 0;
  border: 0;
  border-radius: 8px;
  overflow: hidden;
  background: #ececf4;
  color: #9aa0b8;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.25s ease;
  }

  &:not(:disabled):hover img {
    transform: scale(1.04);
  }

  &:focus-visible {
    outline: 2px solid $primary;
    outline-offset: 2px;
  }

  &:disabled {
    cursor: default;
  }

  &--selected {
    box-shadow: inset 0 0 0 3px #21ba45;
  }

  &--locked:not(&--selected) {
    img {
      opacity: 0.72;
    }
  }

  &__fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
  }

  &__mark {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border: 2px solid #fff;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.28);
    color: #fff;
    pointer-events: none;

    &--on {
      border-color: #21ba45;
      background: #21ba45;
    }

    &--taken {
      border-color: rgba(255, 255, 255, 0.8);
      background: rgba(40, 47, 83, 0.55);
    }
  }

  &__video {
    position: absolute;
    left: 6px;
    bottom: 6px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 1px;
    max-width: calc(100% - 12px);
    padding: 2px 6px 2px 2px;
    border-radius: 999px;
    background: rgba(18, 18, 18, 0.72);
    color: #fff;
    font-size: 11px;
    line-height: 1.2;
    pointer-events: none;
  }
}
</style>
