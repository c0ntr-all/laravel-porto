<template>
  <div
    class="movie-folder-actions"
    :class="`movie-folder-actions--${variant}`"
    @click.prevent.stop
  >
    <q-btn
      v-for="action in actions"
      :key="action.slug"
      :class="{ 'movie-folder-actions__btn--active': action.active }"
      :loading="folderStore.isPending(movie.id, action.slug)"
      :icon="action.icon"
      :label="variant === 'row' ? action.label : undefined"
      :round="variant === 'overlay'"
      unelevated
      no-caps
      dense
      type="button"
      @click="folderStore.toggleMovieFolder(movie, action.slug)"
    >
      <q-tooltip v-if="variant === 'overlay'">{{ action.label }}</q-tooltip>
    </q-btn>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IMovie } from 'src/types/Movie'
import { useMovieFolderStore } from 'src/stores/modules/movieFolderStore'
import {
  SYSTEM_MOVIE_FOLDER_LABELS,
  SystemMovieFolderEnum
} from 'src/enums/Movie/SystemMovieFolderEnum'

const props = defineProps<{
  movie: IMovie
  variant: 'overlay' | 'row'
}>()

const folderStore = useMovieFolderStore()

const actions = computed(() => ([
  {
    slug: SystemMovieFolderEnum.WATCHLIST,
    label: SYSTEM_MOVIE_FOLDER_LABELS[SystemMovieFolderEnum.WATCHLIST],
    active: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHLIST),
    icon: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHLIST)
      ? 'bookmark'
      : 'bookmark_border'
  },
  {
    slug: SystemMovieFolderEnum.WATCHED,
    label: SYSTEM_MOVIE_FOLDER_LABELS[SystemMovieFolderEnum.WATCHED],
    active: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHED),
    icon: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHED)
      ? 'check_circle'
      : 'check_circle_outline'
  }
]))
</script>

<style lang="scss" scoped>
.movie-folder-actions {
  display: flex;
  align-items: center;

  :deep(.q-btn) {
    background: rgba(255, 255, 255, 0.94);
    color: #282f53;
    box-shadow: 0 4px 12px rgba(18, 18, 18, 0.18);
  }

  :deep(.movie-folder-actions__btn--active) {
    background: $primary;
    color: #fff;
  }

  &--overlay {
    position: absolute;
    left: 8px;
    right: 8px;
    bottom: 8px;
    z-index: 2;
    justify-content: center;
    gap: 8px;
  }

  &--row {
    flex-direction: row;
    flex-shrink: 0;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-content: center;
    gap: 8px;
    padding: 4px 4px 4px 0;
  }
}
</style>
