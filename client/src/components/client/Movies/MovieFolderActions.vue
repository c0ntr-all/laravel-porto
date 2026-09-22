<template>
  <div
    class="movie-folder-actions"
    :class="`movie-folder-actions--${variant}`"
    @click.prevent.stop
  >
    <q-btn
      v-for="action in actions"
      :key="action.slug"
      :class="{
        'movie-folder-actions__btn--active': action.active,
        'movie-folder-actions__btn--heart': action.slug === SystemMovieFolderEnum.FAVORITES
      }"
      :loading="folderStore.isPending(movie.id, folderId(action.slug))"
      :icon="action.icon"
      :label="variant === 'row' && !action.iconOnly ? action.label : undefined"
      :round="variant === 'overlay' || Boolean(action.iconOnly)"
      unelevated
      no-caps
      dense
      type="button"
      @click="folderStore.toggleMovieFolder(movie, action.slug)"
    >
      <q-tooltip v-if="variant === 'overlay' || action.iconOnly">{{ action.label }}</q-tooltip>
    </q-btn>

    <q-select
      v-if="variant === 'row'"
      class="movie-folder-actions__select"
      dense
      outlined
      emit-value
      map-options
      :model-value="null"
      :options="folderOptions"
      label="Папки"
      options-dense
      @update:model-value="onPickFolder"
    >
      <template #option="scope">
        <q-item v-bind="scope.itemProps">
          <q-item-section>{{ scope.opt.label }}</q-item-section>
          <q-item-section v-if="isSelected(scope.opt.value)" side>
            <q-icon name="check" color="primary" />
          </q-item-section>
        </q-item>
      </template>
    </q-select>
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

import {
  isMovieInFolderRecord
} from 'src/utils/movieFolders'

const props = defineProps<{
  movie: IMovie
  variant: 'overlay' | 'row'
}>()

const folderStore = useMovieFolderStore()

const folderOptions = computed(() => folderStore.sortedFolders.map(folder => ({
  label: folder.name,
  value: folder.id
})))

function folderId(slug: SystemMovieFolderEnum): string {
  return folderStore.folderBySlug(slug)?.id ?? slug
}

function isSelected(id: string): boolean {
  const folder = folderStore.folderById(id)

  return folder ? isMovieInFolderRecord(props.movie, folder) : false
}

async function onPickFolder(value: string | number | null): Promise<void> {
  if (value === null || value === '') {
    return
  }

  const folder = folderStore.folderById(String(value))

  if (!folder) {
    return
  }

  await folderStore.toggleMovieInFolder(props.movie, folder)
}

const actions = computed(() => ([
  {
    slug: SystemMovieFolderEnum.WATCHLIST,
    label: SYSTEM_MOVIE_FOLDER_LABELS[SystemMovieFolderEnum.WATCHLIST],
    iconOnly: false,
    active: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHLIST),
    icon: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHLIST)
      ? 'bookmark'
      : 'bookmark_border'
  },
  {
    slug: SystemMovieFolderEnum.WATCHED,
    label: SYSTEM_MOVIE_FOLDER_LABELS[SystemMovieFolderEnum.WATCHED],
    iconOnly: false,
    active: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHED),
    icon: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.WATCHED)
      ? 'check_circle'
      : 'check_circle_outline'
  },
  {
    slug: SystemMovieFolderEnum.FAVORITES,
    label: SYSTEM_MOVIE_FOLDER_LABELS[SystemMovieFolderEnum.FAVORITES],
    iconOnly: true,
    active: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.FAVORITES),
    icon: folderStore.isMovieInFolder(props.movie, SystemMovieFolderEnum.FAVORITES)
      ? 'favorite'
      : 'favorite_border'
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

  :deep(.movie-folder-actions__btn--heart.movie-folder-actions__btn--active) {
    background: #fff;
    color: #e53935;
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

  &__select {
    width: 180px;
  }
}
</style>
