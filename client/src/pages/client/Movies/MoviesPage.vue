<template>
  <MoviesShell>
  <header class="movies-page__head">
    <div class="movies-page__intro">
      <h1 class="movies-page__title">Все фильмы</h1>
      <p v-if="totalLabel" class="movies-page__total">
        {{ totalLabel }}
      </p>
      <p v-else-if="movieStore.isMoviesLoading" class="movies-page__total movies-page__total--muted">
        Считаем каталог…
      </p>
    </div>
  </header>

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

  <div class="movies-filters">
    <q-select
      v-model="genreFilter"
      class="movies-filters__field"
      label="Жанры"
      outlined
      dense
      multiple
      use-chips
      use-input
      input-debounce="0"
      emit-value
      map-options
      clearable
      options-dense
      :options="filteredGenreOptions"
      :loading="genreStore.isLoading"
      @filter="filterGenres"
      @update:model-value="onFiltersChange"
    >
      <template #prepend>
        <q-icon name="category" />
      </template>
    </q-select>

    <q-select
      v-model="yearFilter"
      class="movies-filters__field"
      label="Годы"
      outlined
      dense
      multiple
      use-chips
      use-input
      input-debounce="0"
      emit-value
      map-options
      clearable
      options-dense
      :options="filteredYearOptions"
      @filter="filterYears"
      @update:model-value="onFiltersChange"
    >
      <template #prepend>
        <q-icon name="calendar_month" />
      </template>
    </q-select>

    <q-select
      v-model="sortFilter"
      class="movies-filters__sort"
      label="Сортировка"
      outlined
      dense
      emit-value
      map-options
      options-dense
      :options="sortOptions"
      @update:model-value="onFiltersChange"
    >
      <template #prepend>
        <q-icon name="sort" />
      </template>
    </q-select>

    <q-btn
      v-if="hasActiveFilters"
      class="movies-filters__clear"
      flat
      dense
      no-caps
      color="primary"
      icon="filter_alt_off"
      label="Сбросить"
      @click="clearFilters"
    />
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
      :title="emptyTitle"
      :body="emptyBody"
    />
  </q-card>
  </MoviesShell>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { useMovieStore } from 'src/stores/modules/movieStore'
import { useMovieFolderStore } from 'src/stores/modules/movieFolderStore'
import { useMovieGenreStore } from 'src/stores/modules/movieGenreStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { MovieTypeEnum, MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { MoviesViewModeEnum } from 'src/enums/Movie/MoviesViewModeEnum'
import { useMoviesViewMode } from 'src/composables/client/Movies/useMoviesViewMode'
import { formatMoviesCount } from 'src/utils/movieLabels'
import MovieCard from 'src/components/client/Movies/MovieCard.vue'
import MovieCardRow from 'src/components/client/Movies/MovieCardRow.vue'
import MoviesShell from 'src/components/client/Movies/MoviesShell.vue'
import MoviesPageSkeleton from 'src/pages/client/Movies/MoviesPageSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const SORT_NEWEST = '-created_at'
const SORT_RATING_DESC = '-kp_rating'
const SORT_RATING_ASC = 'kp_rating'
const YEAR_FROM = 1888

type SelectOption = {
  label: string
  value: string | number
}

const movieStore = useMovieStore()
const folderStore = useMovieFolderStore()
const genreStore = useMovieGenreStore()
const searchText = ref(movieStore.listTitle)
const typeFilter = ref<MovieTypeEnum | 'all'>(movieStore.listType ?? 'all')
const genreFilter = ref<string[]>([...movieStore.listGenreIds])
const yearFilter = ref<number[]>([...movieStore.listYears])
const sortFilter = ref(movieStore.listSort || SORT_NEWEST)
const { viewMode } = useMoviesViewMode()

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

const sortOptions = [
  { label: 'По дате добавления', value: SORT_NEWEST },
  { label: 'По рейтингу: выше', value: SORT_RATING_DESC },
  { label: 'По рейтингу: ниже', value: SORT_RATING_ASC }
]

const yearOptions = computed<SelectOption[]>(() => {
  const current = new Date().getFullYear()

  return Array.from({ length: current - YEAR_FROM + 1 }, (_, index) => {
    const year = current - index

    return { label: String(year), value: year }
  })
})

const genreOptions = computed<SelectOption[]>(() => (
  genreStore.genres.map(genre => ({
    label: genre.name,
    value: genre.id
  }))
))

const filteredGenreOptions = ref<SelectOption[]>([])
const filteredYearOptions = ref<SelectOption[]>([])

const totalLabel = computed(() => {
  if (movieStore.moviesTotal == null) {
    return ''
  }

  return `${formatMoviesCount(movieStore.moviesTotal)} в каталоге`
})

const hasQuery = computed(() => (
  Boolean(searchText.value.trim()) ||
  typeFilter.value !== 'all' ||
  genreFilter.value.length > 0 ||
  yearFilter.value.length > 0
))

const hasActiveFilters = computed(() => (
  genreFilter.value.length > 0 ||
  yearFilter.value.length > 0 ||
  sortFilter.value !== SORT_NEWEST
))

const emptyTitle = computed(() => (
  hasQuery.value ? 'Ничего не найдено' : 'Фильмов пока нет'
))

const emptyBody = computed(() => (
  hasQuery.value
    ? 'Попробуйте изменить поиск или сбросить фильтры.'
    : 'Когда в каталоге появятся фильмы, они отобразятся здесь.'
))

const { sentinel } = useScrollSentinel(
  () => { void movieStore.getMovies({ append: true }) },
  () => movieStore.hasMoreMovies && !movieStore.isMoviesLoading && !movieStore.isMoviesLoadingMore
)

function resolvedType(): MovieTypeEnum | null {
  return typeFilter.value === 'all' ? null : typeFilter.value
}

function resolvedSort(): string | null {
  return sortFilter.value === SORT_NEWEST ? null : sortFilter.value
}

function normalizeFilters(): void {
  if (!Array.isArray(genreFilter.value)) {
    genreFilter.value = []
  }

  if (!Array.isArray(yearFilter.value)) {
    yearFilter.value = []
  }

  if (!sortFilter.value) {
    sortFilter.value = SORT_NEWEST
  }
}

function listParams() {
  normalizeFilters()

  return {
    title: searchText.value,
    type: resolvedType(),
    genre_ids: genreFilter.value,
    years: yearFilter.value,
    sort: resolvedSort()
  }
}

function filterSelectOptions(
  needle: string,
  options: SelectOption[],
  update: (callback: () => void) => void,
  target: { value: SelectOption[] }
): void {
  update(() => {
    const query = needle.trim().toLowerCase()

    target.value = query
      ? options.filter(option => option.label.toLowerCase().includes(query))
      : options
  })
}

function filterGenres(val: string, update: (callback: () => void) => void): void {
  filterSelectOptions(val, genreOptions.value, update, filteredGenreOptions)
}

function filterYears(val: string, update: (callback: () => void) => void): void {
  filterSelectOptions(val, yearOptions.value, update, filteredYearOptions)
}

function onSearch(value: string | number | null): void {
  searchText.value = value == null ? '' : String(value)
  void movieStore.getMovies(listParams())
}

function onTypeChange(): void {
  void movieStore.getMovies(listParams())
}

function onFiltersChange(): void {
  void movieStore.getMovies(listParams())
}

function clearFilters(): void {
  genreFilter.value = []
  yearFilter.value = []
  sortFilter.value = SORT_NEWEST
  void movieStore.getMovies(listParams())
}

onMounted(() => {
  filteredGenreOptions.value = genreOptions.value
  filteredYearOptions.value = yearOptions.value

  void folderStore.getFolders()

  if (!genreStore.genres.length) {
    void genreStore.getGenres().then(() => {
      filteredGenreOptions.value = genreOptions.value
    })
  }

  if (!movieStore.movies.length) {
    void movieStore.getMovies(listParams())
  }
})
</script>

<style lang="scss" scoped>
.movies-page {
  &__head {
    margin-bottom: 1.25rem;
  }

  &__title {
    margin: 0 0 4px;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__total {
    margin: 0;
    color: #777a8f;
    font-size: 15px;
    line-height: 1.4;

    &--muted {
      opacity: 0.75;
    }
  }
}

.movies-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 12px;

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

.movies-filters {
  display: flex;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 1.25rem;

  &__field {
    flex: 1 1 220px;
    min-width: 200px;
    max-width: 360px;
  }

  &__sort {
    flex: 0 1 240px;
    min-width: 200px;
  }

  &__clear {
    margin-top: 2px;
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
