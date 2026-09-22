<template>
  <aside class="movie-folders-sidebar">
    <div class="movie-folders-sidebar__title">Папки</div>

    <q-list class="movie-folders-sidebar__list" padding dense>
      <q-item
        clickable
        exact
        :active="isCatalog"
        active-class="movie-folders-sidebar__item--active"
        :to="{ name: 'movies' }"
      >
        <q-item-section avatar>
          <q-icon name="movie" />
        </q-item-section>
        <q-item-section>Все фильмы</q-item-section>
      </q-item>

      <q-item
        v-for="folder in folderStore.folders"
        :key="folder.id"
        clickable
        :active="isFolderActive(folder.id)"
        active-class="movie-folders-sidebar__item--active"
        :to="{ name: 'movie-folder', params: { id: folder.id } }"
      >
        <q-item-section avatar>
          <q-icon :name="folderIcon(folder.slug)" />
        </q-item-section>
        <q-item-section>{{ folder.name }}</q-item-section>
        <q-item-section side>
          <span class="movie-folders-sidebar__count">{{ folder.movies_count }}</span>
        </q-item-section>
      </q-item>
    </q-list>

    <div v-if="folderStore.isLoading && !folderStore.folders.length" class="q-px-md q-pb-md">
      <q-skeleton type="text" width="80%" />
      <q-skeleton type="text" width="60%" />
      <q-skeleton type="text" width="70%" />
    </div>
  </aside>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useMovieFolderStore } from 'src/stores/modules/movieFolderStore'
import { SystemMovieFolderEnum } from 'src/enums/Movie/SystemMovieFolderEnum'

const route = useRoute()
const folderStore = useMovieFolderStore()

const isCatalog = computed(() => route.name === 'movies')

function isFolderActive(folderId: string): boolean {
  return route.name === 'movie-folder' && String(route.params.id) === folderId
}

function folderIcon(slug: string | null): string {
  if (slug === SystemMovieFolderEnum.WATCHLIST) {
    return 'bookmark'
  }

  if (slug === SystemMovieFolderEnum.WATCHED) {
    return 'check_circle'
  }

  if (slug === SystemMovieFolderEnum.FAVORITES) {
    return 'favorite'
  }

  return 'folder'
}
</script>

<style lang="scss" scoped>
.movie-folders-sidebar {
  padding: 12px 8px 16px;
  border-radius: 16px;
  background: #fff;
  border: 1px solid rgba(40, 47, 83, 0.08);
  box-shadow: 0 6px 16px rgba(40, 47, 83, 0.06);

  &__title {
    padding: 4px 12px 8px;
    font-size: 15px;
    font-weight: 700;
    color: #282f53;
  }

  &__count {
    color: #777a8f;
    font-size: 13px;
  }

  &__item--active {
    color: $primary;
    background: rgba(108, 95, 252, 0.08);
  }
}
</style>
