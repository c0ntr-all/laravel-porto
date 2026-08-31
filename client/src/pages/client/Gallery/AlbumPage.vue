<template>
  <div class="q-mb-md">
    <AppBackButton link="/gallery" text="Back to albums" />
  </div>

  <AlbumPageSkeleton v-if="galleryStore.isAlbumLoading && !galleryStore.album" />

  <template v-else-if="galleryStore.album">
    <div class="album-head q-mb-lg">
      <div class="album-head__cover">
        <q-img
          v-if="hasCover"
          :src="galleryStore.album.image"
          :alt="galleryStore.album.name"
          class="album-head__image"
          fit="cover"
        >
          <template #error>
            <div class="album-head__placeholder">
              <q-icon name="photo_library" size="48px" />
            </div>
          </template>
        </q-img>
        <div v-else class="album-head__placeholder">
          <q-icon name="photo_library" size="48px" />
        </div>
        <button
          class="album-head__cover-edit"
          type="button"
          @click="showCoverDialog = true"
        >
          <q-icon name="photo_camera" size="18px" />
          Change cover
        </button>
      </div>

      <div class="album-head__info">
        <div class="album-head__title-row">
          <h1 class="album-head__name">{{ galleryStore.album.name }}</h1>
          <q-chip
            v-if="galleryStore.album.is_system"
            color="primary"
            text-color="white"
            size="sm"
            dense
          >
            System
          </q-chip>
        </div>
        <p v-if="galleryStore.album.description" class="album-head__description">
          {{ galleryStore.album.description }}
        </p>
        <div class="album-head__stats">
          <span>{{ totalLabel }}</span>
          <span v-if="mediaCounts.photos">{{ photosLabel }}</span>
          <span v-if="mediaCounts.videos">{{ videosLabel }}</span>
        </div>
        <div class="album-head__actions">
          <GalleryUploadButton v-if="!galleryStore.album.is_system" />
          <q-btn
            outline
            no-caps
            color="primary"
            icon="image"
            label="Change cover"
            @click="showCoverDialog = true"
          />
        </div>
      </div>
    </div>

    <div class="album-body">
      <div v-if="showMediaFilter" class="album-body__toolbar">
        <q-btn-toggle
          v-model="mediaFilter"
          unelevated
          no-caps
          toggle-color="primary"
          :options="filterOptions"
        />
      </div>

      <div v-if="visibleMedia.length" class="media-grid">
        <GalleryMediaCard
          v-for="item in visibleMedia"
          :key="item.id"
          :media="item"
          @click="openCarousel(item.id)"
        />
      </div>

      <AppNoResultsPlug
        v-else-if="galleryStore.album.media.length"
        title="Nothing in this filter"
        body="Try another type or add more media."
      />

      <AppNoResultsPlug
        v-else
        title="This album is empty"
        :body="emptyBody"
      />
    </div>

    <GalleryCarousel
      v-model="showCarousel"
      v-model:current-slide-id="currentSlideId"
      :slides="galleryStore.album.media"
    />

    <GalleryCoverDialog v-model="showCoverDialog" />
  </template>

  <AppNoResultsPlug
    v-else
    title="Album not found"
    body="This album does not exist or is unavailable."
  />
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { countMediaByKind } from 'src/api/mappers/gallery.mapper'
import { GalleryMediaFilter } from 'src/types/gallery'
import { isGalleryVideo, hasAlbumCover } from 'src/utils/gallery'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import GalleryMediaCard from 'src/components/client/Gallery/GalleryMediaCard.vue'
import GalleryCarousel from 'src/components/client/Gallery/GalleryCarousel.vue'
import AlbumPageSkeleton from 'src/pages/client/Gallery/AlbumPageSkeleton.vue'
import GalleryUploadButton from 'src/components/client/Gallery/GalleryUploadButton.vue'
import GalleryCoverDialog from 'src/components/client/Gallery/GalleryCoverDialog.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  id: string
}>()

const galleryStore = useGalleryStore()
const mediaFilter = ref<GalleryMediaFilter>('all')
const showCarousel = ref(false)
const currentSlideId = ref('')
const showCoverDialog = ref(false)

const mediaCounts = computed(() => countMediaByKind(galleryStore.album?.media ?? []))

const totalLabel = computed(() => {
  const count = galleryStore.album?.media_count ?? 0

  return count === 1 ? '1 item' : `${count} items`
})

const photosLabel = computed(() => (
  mediaCounts.value.photos === 1 ? '1 photo' : `${mediaCounts.value.photos} photos`
))

const videosLabel = computed(() => (
  mediaCounts.value.videos === 1 ? '1 video' : `${mediaCounts.value.videos} videos`
))

const filterOptions = computed(() => {
  const options = [{ label: 'All', value: 'all' }]

  if (mediaCounts.value.photos) {
    options.push({ label: 'Photos', value: 'photo' })
  }

  if (mediaCounts.value.videos) {
    options.push({ label: 'Videos', value: 'video' })
  }

  return options
})

const showMediaFilter = computed(() => (
  mediaCounts.value.photos > 0 && mediaCounts.value.videos > 0
))

const emptyBody = computed(() => (
  galleryStore.album?.is_system
    ? 'This system album is filled automatically.'
    : 'Add photos or videos from a device, a link, or a local path.'
))

const hasCover = computed(() => hasAlbumCover(galleryStore.album?.image))

const visibleMedia = computed(() => {
  const items = galleryStore.album?.media ?? []

  if (mediaFilter.value === 'all') {
    return items
  }

  return items.filter(item => (
    mediaFilter.value === 'video' ? isGalleryVideo(item) : !isGalleryVideo(item)
  ))
})

function openCarousel(id: string): void {
  currentSlideId.value = id
  showCarousel.value = true
}

watch(() => props.id, (id) => {
  mediaFilter.value = 'all'
  void galleryStore.getAlbum(id)
}, { immediate: true })
</script>

<style lang="scss" scoped>
.album-head {
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;

  &__cover {
    position: relative;
    flex: 0 0 220px;
    width: 220px;
    overflow: hidden;
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(40, 47, 83, 0.12);

    &:hover .album-head__cover-edit {
      opacity: 1;
    }
  }

  &__cover-edit {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px;
    border: 0;
    background: linear-gradient(transparent, rgba(18, 18, 18, 0.78));
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s ease;
  }

  @media (hover: none) {
    .album-head__cover-edit {
      opacity: 1;
    }
  }

  &__image,
  &__placeholder {
    width: 220px;
    height: 220px;
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9aa0b8;
    background:
      linear-gradient(135deg, rgba(108, 95, 252, 0.12), rgba(38, 166, 154, 0.08));
  }

  &__info {
    min-width: 0;
    flex: 1;
    padding-top: 0.25rem;
  }

  &__title-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 0.5rem;
  }

  &__name {
    margin: 0;
    font-size: 36px;
    line-height: 1.15;
    font-weight: 700;
    color: #282f53;
  }

  &__description {
    margin: 0 0 0.75rem;
    max-width: 640px;
    color: #55586d;
    line-height: 1.45;
  }

  &__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem 0.75rem;
    margin-bottom: 1rem;
    font-size: 14px;
    color: #777a8f;
  }

  &__actions {
    display: flex;
    gap: 8px;
  }
}

.album-body {
  padding: 1rem;
  border-radius: 16px;
  background: #fff;

  &__toolbar {
    margin-bottom: 1rem;
  }
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
  gap: 8px;
}

@media (max-width: 700px) {
  .album-head {
    flex-direction: column;

    &__cover,
    &__image,
    &__placeholder {
      width: 100%;
      max-width: 280px;
    }

    &__image,
    &__placeholder {
      height: auto;
      aspect-ratio: 1;
    }

    &__name {
      font-size: 28px;
    }
  }
}
</style>
