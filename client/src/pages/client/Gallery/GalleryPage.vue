<template>
  <GalleryPageSkeleton v-if="galleryStore.isAlbumsLoading && !galleryStore.albums.length" />

  <template v-else>
    <div class="gallery-toolbar">
      <div class="gallery-toolbar__count">
        {{ albumsCountLabel }}
      </div>
    </div>

    <div v-if="galleryStore.albums.length" class="gallery-grid">
      <GalleryAlbumCard
        v-for="album in galleryStore.albums"
        :key="album.id"
        :album="album"
      />
    </div>

    <q-card v-else class="q-mb-md" flat>
      <AppNoResultsPlug
        title="No albums yet"
        body="Albums will appear here once they are created."
      />
    </q-card>
  </template>
</template>

<script lang="ts" setup>
import { computed, onMounted } from 'vue'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import GalleryAlbumCard from 'src/components/client/Gallery/GalleryAlbumCard.vue'
import GalleryPageSkeleton from 'src/pages/client/Gallery/GalleryPageSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const galleryStore = useGalleryStore()

const albumsCountLabel = computed(() => {
  const count = galleryStore.albums.length

  if (count === 1) {
    return '1 album'
  }

  return `${count} albums`
})

onMounted(() => {
  void galleryStore.getAlbums()
})
</script>

<style lang="scss" scoped>
.gallery-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;

  &__count {
    font-size: 14px;
    color: #777a8f;
  }
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.5rem 1.25rem;
}
</style>
