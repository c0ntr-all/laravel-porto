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
        <GalleryInlineDescription
          class="album-head__description"
          :text="galleryStore.album.description"
          @save="saveAlbumDescription"
        />
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
      <div v-if="albumTags.length" class="album-body__tags">
        <q-chip
          v-for="tag in albumTags"
          :key="tag.id"
          clickable
          :outline="!selectedTagIds.includes(tag.id)"
          :color="selectedTagIds.includes(tag.id) ? 'primary' : undefined"
          :text-color="selectedTagIds.includes(tag.id) ? 'white' : 'primary'"
          @click="toggleTag(tag.id)"
        >
          {{ tag.name }}
        </q-chip>
      </div>

      <div class="album-body__toolbar">
        <q-btn-toggle
          v-if="showMediaFilter"
          v-model="mediaFilter"
          unelevated
          no-caps
          toggle-color="primary"
          :options="filterOptions"
        />

        <q-btn-toggle
          v-model="sortDirection"
          unelevated
          no-caps
          toggle-color="primary"
          :options="sortOptions"
        />

        <q-btn
          unelevated
          no-caps
          icon="calendar_month"
          label="Группировка по дате"
          :color="groupByDate ? 'primary' : 'grey-2'"
          :text-color="groupByDate ? 'white' : 'primary'"
          @click="groupByDate = !groupByDate"
        />
      </div>

      <template v-if="pagedMedia.length">
        <template v-if="groupByDate">
          <section
            v-for="group in groupedMedia"
            :key="group.day || 'none'"
            class="media-day"
          >
            <h2 class="media-day__title">{{ group.label }}</h2>
            <div class="media-grid">
              <GalleryMediaCard
                v-for="item in group.items"
                :key="item.id"
                :media="item"
                show-menu
                @click="openCarousel(item.id)"
                @add-tag="openTagDialog(item)"
                @delete="confirmDelete(item)"
              />
            </div>
          </section>
        </template>

        <div v-else class="media-grid">
          <GalleryMediaCard
            v-for="item in pagedMedia"
            :key="item.id"
            :media="item"
            show-menu
            @click="openCarousel(item.id)"
            @add-tag="openTagDialog(item)"
            @delete="confirmDelete(item)"
          />
        </div>

        <div v-if="hasMoreMedia" class="album-body__more">
          <q-btn
            unelevated
            no-caps
            color="primary"
            label="Ещё 30"
            @click="loadMoreMedia"
          />
        </div>
      </template>

      <AppNoResultsPlug
        v-else-if="galleryStore.album.media.length"
        title="Nothing in this filter"
        body="Try another type, tag, or add more media."
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
      :slides="filteredMedia"
    />

    <GalleryCoverDialog v-model="showCoverDialog" />

    <q-dialog v-model="showTagDialog">
      <q-card class="tag-dialog">
        <q-card-section class="row items-center no-wrap">
          <div class="text-h6">Добавить тег</div>
          <q-space />
          <q-btn v-close-popup icon="close" flat round dense />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <GalleryViewerTags v-if="liveTagTarget" :item="liveTagTarget" />
        </q-card-section>
      </q-card>
    </q-dialog>
  </template>

  <AppNoResultsPlug
    v-else
    title="Album not found"
    body="This album does not exist or is unavailable."
  />
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { Dialog } from 'quasar'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { countMediaByKind } from 'src/api/mappers/gallery.mapper'
import { GalleryMediaFilter, IGalleryMediaItem } from 'src/types/gallery'
import {
  GALLERY_MEDIA_PAGE_SIZE,
  groupGalleryMediaByDay,
  hasAlbumCover,
  isGalleryVideo,
  mediaHasAnyTag,
  sortGalleryMedia,
  uniqueMediaTags
} from 'src/utils/gallery'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import GalleryMediaCard from 'src/components/client/Gallery/GalleryMediaCard.vue'
import GalleryCarousel from 'src/components/client/Gallery/GalleryCarousel.vue'
import AlbumPageSkeleton from 'src/pages/client/Gallery/AlbumPageSkeleton.vue'
import GalleryUploadButton from 'src/components/client/Gallery/GalleryUploadButton.vue'
import GalleryCoverDialog from 'src/components/client/Gallery/GalleryCoverDialog.vue'
import GalleryInlineDescription from 'src/components/client/Gallery/GalleryInlineDescription.vue'
import GalleryViewerTags from 'src/components/client/Gallery/GalleryViewerTags.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  id: string
}>()

const galleryStore = useGalleryStore()
const mediaFilter = ref<GalleryMediaFilter>('all')
const sortDirection = ref<'asc' | 'desc'>('desc')
const groupByDate = ref(false)
const selectedTagIds = ref<string[]>([])
const visibleLimit = ref(GALLERY_MEDIA_PAGE_SIZE)
const showCarousel = ref(false)
const currentSlideId = ref('')
const showCoverDialog = ref(false)
const showTagDialog = ref(false)
const tagTarget = ref<IGalleryMediaItem | null>(null)

const sortOptions = [
  { label: 'Сначала новые', value: 'desc' },
  { label: 'Сначала старые', value: 'asc' }
]

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

const albumTags = computed(() => uniqueMediaTags(galleryStore.album?.media ?? []))

const selectedTagSet = computed(() => new Set(selectedTagIds.value))

const filteredMedia = computed(() => {
  const items = galleryStore.album?.media ?? []
  const byType = mediaFilter.value === 'all'
    ? items
    : items.filter(item => (
      mediaFilter.value === 'video' ? isGalleryVideo(item) : !isGalleryVideo(item)
    ))
  const byTags = selectedTagSet.value.size
    ? byType.filter(item => mediaHasAnyTag(item, selectedTagSet.value))
    : byType

  return sortGalleryMedia(byTags, sortDirection.value)
})

const pagedMedia = computed(() => filteredMedia.value.slice(0, visibleLimit.value))

const hasMoreMedia = computed(() => pagedMedia.value.length < filteredMedia.value.length)

const groupedMedia = computed(() => groupGalleryMediaByDay(pagedMedia.value))

const liveTagTarget = computed(() => (
  galleryStore.album?.media.find(item => item.id === tagTarget.value?.id) ?? null
))

function openCarousel(id: string): void {
  currentSlideId.value = id
  showCarousel.value = true
}

function toggleTag(id: string): void {
  selectedTagIds.value = selectedTagIds.value.includes(id)
    ? selectedTagIds.value.filter(tagId => tagId !== id)
    : [...selectedTagIds.value, id]
}

function loadMoreMedia(): void {
  visibleLimit.value += GALLERY_MEDIA_PAGE_SIZE
}

function openTagDialog(item: IGalleryMediaItem): void {
  tagTarget.value = item
  showTagDialog.value = true
}

function confirmDelete(item: IGalleryMediaItem): void {
  Dialog.create({
    title: 'Удалить файл?',
    message: 'Файл будет удалён из альбома.',
    cancel: { label: 'Отмена', flat: true },
    ok: { label: 'Удалить', color: 'negative' },
    persistent: true
  }).onOk(async () => {
    const ok = await galleryStore.deleteMedia(item)

    if (!ok) {
      return
    }

    if (tagTarget.value?.id === item.id) {
      showTagDialog.value = false
      tagTarget.value = null
    }

    if (currentSlideId.value === item.id) {
      showCarousel.value = false
    }
  })
}

async function saveAlbumDescription(value: string | null): Promise<void> {
  await galleryStore.updateAlbum({ description: value }, { silent: true })
}

function resetView(): void {
  mediaFilter.value = 'all'
  sortDirection.value = 'desc'
  groupByDate.value = false
  selectedTagIds.value = []
  visibleLimit.value = GALLERY_MEDIA_PAGE_SIZE
  showCarousel.value = false
  showTagDialog.value = false
  tagTarget.value = null
}

watch(() => props.id, (id) => {
  resetView()
  void galleryStore.getAlbum(id)
}, { immediate: true })

watch([mediaFilter, selectedTagIds, sortDirection], () => {
  visibleLimit.value = GALLERY_MEDIA_PAGE_SIZE
})

watch(albumTags, (tags) => {
  const known = new Set(tags.map(tag => tag.id))
  selectedTagIds.value = selectedTagIds.value.filter(id => known.has(id))
})
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

  &__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 1rem;
  }

  &__toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 1rem;
  }

  &__more {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
  }
}

.media-day {
  & + & {
    margin-top: 1.25rem;
  }

  &__title {
    margin: 0 0 0.75rem;
    font-size: 16px;
    font-weight: 600;
    color: #282f53;
  }
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
  gap: 8px;
}

.tag-dialog {
  width: 420px;
  max-width: 92vw;
  border-radius: 16px;
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
