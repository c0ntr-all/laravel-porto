<template>
  <q-dialog
    v-model="show"
    persistent
  >
    <q-card class="album-picker">
      <header class="album-picker__header">
        <h2 class="album-picker__title">
          Выбрать альбом
        </h2>
        <q-btn
          flat
          round
          dense
          icon="close"
          aria-label="Закрыть"
          @click="cancel"
        />
      </header>

      <q-separator />

      <div class="album-picker__toolbar">
        <q-input
          v-model="search"
          dense
          outlined
          clearable
          placeholder="Поиск по названию"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>

      <div class="album-picker__body">
        <div v-if="isLoading && !albums.length" class="album-picker__skeletons">
          <div
            v-for="index in 5"
            :key="index"
            class="album-row album-row--skeleton"
          >
            <q-skeleton type="rect" width="64px" height="64px" />
            <span class="album-row__text">
              <q-skeleton type="text" width="48%" />
              <q-skeleton type="text" width="70%" />
            </span>
          </div>
        </div>

        <div v-else-if="error" class="album-picker__state">
          <q-icon name="cloud_off" size="36px" />
          <div>{{ error }}</div>
          <q-btn
            flat
            no-caps
            color="primary"
            label="Повторить"
            @click="load"
          />
        </div>

        <div v-else-if="!filteredAlbums.length" class="album-picker__state">
          <q-icon name="photo_album" size="36px" />
          <div>{{ search.trim() ? 'Ничего не найдено' : 'Альбомов пока нет' }}</div>
        </div>

        <div v-else class="album-picker__list">
          <button
            v-for="album in filteredAlbums"
            :key="album.id"
            type="button"
            class="album-row"
            :class="{ 'album-row--selected': selectedId === album.id }"
            :aria-pressed="selectedId === album.id"
            @click="select(album)"
          >
            <img
              v-if="cover(album)"
              :src="cover(album)"
              alt=""
              class="album-row__cover"
              draggable="false"
            >
            <span
              v-else
              class="album-row__cover album-row__cover--empty"
            >
              <q-icon name="photo_library" size="24px" />
            </span>
            <span class="album-row__text">
              <span class="album-row__name ellipsis">{{ album.name }}</span>
              <span
                v-if="album.description"
                class="album-row__desc ellipsis-2-lines"
              >
                {{ album.description }}
              </span>
              <span class="album-row__meta">
                <q-icon name="collections" size="14px" />
                {{ album.media_count }}
              </span>
            </span>
            <q-icon
              v-if="selectedId === album.id"
              name="check_circle"
              color="positive"
              size="20px"
            />
          </button>
        </div>
      </div>

      <q-separator />

      <footer class="album-picker__footer">
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
          :disable="!selected"
          @click="confirm"
        />
      </footer>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { galleryApi } from 'src/api/requests/galleryApi'
import { mapGalleryAlbumsResponse } from 'src/api/mappers/gallery.mapper'
import { IPostAlbumSubject } from 'src/types'
import { IGalleryAlbum } from 'src/types/gallery'
import { handleApiError } from 'src/utils/jsonapi'
import { hasAlbumCover, resolveMediaUrl } from 'src/utils/gallery'

const emit = defineEmits<{
  select: [album: IPostAlbumSubject]
}>()

const show = defineModel<boolean>({ default: false })

const albums = ref<IGalleryAlbum[]>([])
const selected = ref<IPostAlbumSubject | null>(null)
const search = ref('')
const isLoading = ref(false)
const error = ref<string | null>(null)
let requestId = 0

const selectedId = computed(() => selected.value?.id ?? null)

const attachableAlbums = computed(() =>
  albums.value.filter(album => !album.is_system)
)

const filteredAlbums = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) {
    return attachableAlbums.value
  }

  return attachableAlbums.value.filter(album =>
    album.name.toLowerCase().includes(query) ||
    (album.description ?? '').toLowerCase().includes(query)
  )
})

watch(show, isOpen => {
  if (!isOpen) {
    return
  }

  selected.value = null
  search.value = ''
  error.value = null
  void load()
})

async function load () {
  const currentRequest = ++requestId
  isLoading.value = true
  error.value = null

  try {
    const response = await galleryApi.getAlbums()

    if (currentRequest !== requestId) {
      return
    }

    albums.value = mapGalleryAlbumsResponse(response)
  } catch (err) {
    if (currentRequest !== requestId) {
      return
    }

    error.value = 'Не удалось загрузить альбомы'
    handleApiError(err)
  } finally {
    if (currentRequest === requestId) {
      isLoading.value = false
    }
  }
}

function select (album: IGalleryAlbum) {
  selected.value = {
    id: album.id,
    name: album.name,
    description: album.description,
    image: album.image || null,
    media_count: album.media_count
  }
}

function cover (album: IGalleryAlbum): string {
  if (!hasAlbumCover(album.image)) {
    return ''
  }

  return resolveMediaUrl(album.image)
}

function cancel () {
  show.value = false
}

function confirm () {
  if (!selected.value) {
    return
  }

  show.value = false
  emit('select', selected.value)
}
</script>

<style lang="scss" scoped>
.album-picker {
  display: flex;
  flex-direction: column;
  width: min(560px, 94vw);
  height: min(680px, 88vh);
  max-height: 88vh;
  border-radius: 16px;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex: none;
    padding: 14px 8px 14px 18px;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #282f53;
  }

  &__toolbar {
    flex: none;
    padding: 12px 12px 0;
  }

  &__body {
    flex: 1;
    min-height: 0;
    overflow: auto;
    padding: 12px;
  }

  &__state,
  &__skeletons,
  &__list {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  &__state {
    min-height: 240px;
    align-items: center;
    justify-content: center;
    color: #777a8f;
    text-align: center;
  }

  &__footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    flex: none;
    padding: 12px 16px;
  }
}

.album-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px;
  border: 1px solid #e4e7eb;
  border-radius: 14px;
  background: #fff;
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;

  &:hover {
    border-color: $primary;
    background: rgba(108, 95, 252, 0.04);
  }

  &:focus-visible {
    outline: 2px solid $primary;
    outline-offset: 2px;
  }

  &--selected {
    border-color: $primary;
    background: rgba(108, 95, 252, 0.08);
  }

  &--skeleton {
    cursor: default;
    border-color: transparent;

    &:hover {
      background: transparent;
    }
  }

  &__cover {
    width: 64px;
    height: 64px;
    flex: none;
    border-radius: 12px;
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

  &__desc {
    color: #5b6178;
    font-size: 13px;
    line-height: 1.35;
  }

  &__meta {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #777a8f;
    font-size: 12px;
  }
}

.ellipsis-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
