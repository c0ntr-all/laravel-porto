<template>
  <MoviesShell>
    <div class="q-mb-md">
      <h1 class="movie-folder-page__title">{{ folder?.name || 'Папка' }}</h1>
      <div class="movie-folder-page__caption">
        Фильмы в этой папке
      </div>
    </div>

    <div class="row justify-end q-mb-md">
      <q-btn-toggle
        v-model="viewMode"
        unelevated
        dense
        no-caps
        toggle-color="primary"
        color="grey-2"
        text-color="primary"
        :options="viewModeOptions"
      >
        <template #tile>
          <q-icon name="grid_view" size="sm" />
          <q-tooltip>Плитка</q-tooltip>
        </template>
        <template #list>
          <q-icon name="view_agenda" size="sm" />
          <q-tooltip>Список</q-tooltip>
        </template>
      </q-btn-toggle>
    </div>

    <MoviesPageSkeleton
      v-if="isResolving || (folderStore.isFolderMoviesLoading && !folderStore.folderMovies.length)"
      :view-mode="viewMode"
    />

    <template v-else-if="folderStore.folderMovies.length">
      <div v-if="viewMode === MoviesViewModeEnum.TILE" class="movies-grid">
        <MovieCard
          v-for="movie in folderStore.folderMovies"
          :key="movie.id"
          :movie="movie"
        />
      </div>
      <div v-else class="movies-list">
        <MovieCardRow
          v-for="movie in folderStore.folderMovies"
          :key="movie.id"
          :movie="movie"
        />
      </div>

      <div
        v-if="folderStore.hasMoreFolderMovies"
        ref="sentinel"
        class="movies-sentinel"
      />

      <div
        v-if="folderStore.isFolderMoviesLoadingMore"
        class="flex justify-center q-my-md"
      >
        <q-spinner color="primary" size="2em" />
      </div>
    </template>

    <q-card v-else-if="folder" class="q-mb-md" flat>
      <AppNoResultsPlug
        title="В папке пока пусто"
        body="Добавьте фильмы кнопками «Буду смотреть» или «Просмотрено» на карточках."
      />
    </q-card>

    <q-card v-else class="q-mb-md" flat>
      <AppNoResultsPlug
        title="Папка не найдена"
        body="Папка могла быть удалена или недоступна."
      />
    </q-card>
  </MoviesShell>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useMovieFolderStore } from 'src/stores/modules/movieFolderStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { MoviesViewModeEnum } from 'src/enums/Movie/MoviesViewModeEnum'
import MovieCard from 'src/components/client/Movies/MovieCard.vue'
import MovieCardRow from 'src/components/client/Movies/MovieCardRow.vue'
import MoviesShell from 'src/components/client/Movies/MoviesShell.vue'
import MoviesPageSkeleton from 'src/pages/client/Movies/MoviesPageSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  id: string
}>()

const folderStore = useMovieFolderStore()
const viewMode = ref(MoviesViewModeEnum.TILE)
const isResolving = ref(true)
const folder = computed(() => folderStore.folderById(props.id))

const viewModeOptions = [
  { value: MoviesViewModeEnum.TILE, slot: 'tile' },
  { value: MoviesViewModeEnum.LIST, slot: 'list' }
]

const { sentinel } = useScrollSentinel(
  () => { void folderStore.getFolderMovies(props.id, { append: true }) },
  () => (
    folderStore.hasMoreFolderMovies &&
    !folderStore.isFolderMoviesLoading &&
    !folderStore.isFolderMoviesLoadingMore
  )
)

watch(
  () => props.id,
  async (id) => {
    isResolving.value = true
    await folderStore.getFolders()
    await folderStore.getFolder(id)
    await folderStore.getFolderMovies(id)
    isResolving.value = false
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.movie-folder-page {
  &__title {
    margin: 0 0 4px;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__caption {
    color: #777a8f;
    font-size: 15px;
  }
}

.movies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1.5rem 1.25rem;
}

.movies-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.movies-sentinel {
  height: 1px;
}
</style>
