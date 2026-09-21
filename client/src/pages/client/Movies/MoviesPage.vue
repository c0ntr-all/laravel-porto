<template>
  <div class="movies-toolbar">
    <q-input
      v-model="searchText"
      class="movies-toolbar__search"
      label="Поиск по названию"
      outlined
      dense
      debounce="400"
      clearable
      @update:model-value="onSearch"
    >
      <template #prepend>
        <q-icon name="search" />
      </template>
    </q-input>

    <div class="movies-toolbar__actions">
      <q-btn-toggle
        v-model="typeFilter"
        unelevated
        dense
        no-caps
        toggle-color="primary"
        color="grey-2"
        text-color="primary"
        :options="typeOptions"
        @update:model-value="onTypeChange"
      />

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
  </div>

  <MoviesPageSkeleton
    v-if="movieStore.isMoviesLoading && !movieStore.movies.length"
    :view-mode="viewMode"
  />

  <template v-else-if="movieStore.movies.length">
    <div v-if="viewMode === MoviesViewModeEnum.TILE" class="movies-grid">
      <MovieCard
        v-for="movie in movieStore.movies"
        :key="movie.id"
        :movie="movie"
      />
    </div>
    <div v-else class="movies-list">
      <MovieCardRow
        v-for="movie in movieStore.movies"
        :key="movie.id"
        :movie="movie"
      />
    </div>

    <div
      v-if="movieStore.hasMoreMovies"
      ref="sentinel"
      class="movies-sentinel"
    />

    <div
      v-if="movieStore.isMoviesLoadingMore"
      class="flex justify-center q-my-md"
    >
      <q-spinner color="primary" size="2em" />
    </div>
  </template>

  <q-card v-else class="q-mb-md" flat>
    <AppNoResultsPlug
      title="Фильмов пока нет"
      body="Когда в каталоге появятся фильмы, они отобразятся здесь."
    />
  </q-card>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMovieStore } from 'src/stores/modules/movieStore'
import { useMovieFolderStore } from 'src/stores/modules/movieFolderStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { MovieTypeEnum, MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { MoviesViewModeEnum } from 'src/enums/Movie/MoviesViewModeEnum'
import MovieCard from 'src/components/client/Movies/MovieCard.vue'
import MovieCardRow from 'src/components/client/Movies/MovieCardRow.vue'
import MoviesPageSkeleton from 'src/pages/client/Movies/MoviesPageSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const movieStore = useMovieStore()
const folderStore = useMovieFolderStore()
const searchText = ref(movieStore.listTitle)
const typeFilter = ref<MovieTypeEnum | 'all'>(movieStore.listType ?? 'all')
const viewMode = ref(MoviesViewModeEnum.TILE)

const typeOptions = [
  { label: 'Все', value: 'all' },
  { label: MOVIE_TYPE_LABELS[MovieTypeEnum.MOVIE], value: MovieTypeEnum.MOVIE },
  { label: MOVIE_TYPE_LABELS[MovieTypeEnum.TV_SERIES], value: MovieTypeEnum.TV_SERIES },
  { label: MOVIE_TYPE_LABELS[MovieTypeEnum.SHOW], value: MovieTypeEnum.SHOW }
]

const viewModeOptions = [
  { value: MoviesViewModeEnum.TILE, slot: 'tile' },
  { value: MoviesViewModeEnum.LIST, slot: 'list' }
]

const { sentinel } = useScrollSentinel(
  () => { void movieStore.getMovies({ append: true }) },
  () => movieStore.hasMoreMovies && !movieStore.isMoviesLoading && !movieStore.isMoviesLoadingMore
)

function resolvedType(): MovieTypeEnum | null {
  return typeFilter.value === 'all' ? null : typeFilter.value
}

function onSearch(value: string | number | null): void {
  void movieStore.getMovies({
    title: value == null ? '' : String(value),
    type: resolvedType()
  })
}

function onTypeChange(): void {
  void movieStore.getMovies({
    title: searchText.value,
    type: resolvedType()
  })
}

onMounted(() => {
  void folderStore.getFolders()

  if (!movieStore.movies.length) {
    void movieStore.getMovies({
      title: searchText.value,
      type: resolvedType()
    })
  }
})
</script>

<style lang="scss" scoped>
.movies-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 1.25rem;

  &__search {
    flex: 1 1 260px;
    max-width: 420px;
  }

  &__actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
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
