<template>
  <MoviesShell>
    <div class="q-mb-md">
      <AppBackButton link="/movies/franchises" text="К франшизам" />
    </div>

    <div v-if="store.isLoading && !store.franchise" class="movie-franchise-page__skeleton">
      <q-skeleton class="movie-franchise-page__poster" square />
      <div class="movie-franchise-page__info">
        <q-skeleton type="text" width="50%" />
        <q-skeleton type="text" width="90%" />
        <q-skeleton type="text" width="80%" />
      </div>
    </div>

    <template v-else-if="store.franchise">
      <header class="movie-franchise-page__hero">
        <div class="movie-franchise-page__poster">
          <q-img
            v-if="franchiseImageUrl(store.franchise)"
            :src="franchiseImageUrl(store.franchise) ?? ''"
            :alt="store.franchise.name"
            fit="cover"
          >
            <template #error>
              <div class="movie-franchise-page__poster-fallback">
                <q-icon name="collections_bookmark" size="48px" />
              </div>
            </template>
          </q-img>
          <div v-else class="movie-franchise-page__poster-fallback">
            <q-icon name="collections_bookmark" size="48px" />
          </div>
        </div>

        <div class="movie-franchise-page__intro">
          <h1 class="movie-franchise-page__title">{{ store.franchise.name }}</h1>
          <div
            v-if="store.franchise.description"
            class="movie-franchise-page__description"
            v-html="store.franchise.description"
          />
          <p v-else class="movie-franchise-page__description text-grey-6">
            Описание отсутствует
          </p>
        </div>
      </header>

      <section class="movie-franchise-page__movies">
        <div class="movie-franchise-page__movies-head">
          <h2 class="movie-franchise-page__movies-title">Фильмы</h2>
          <q-select
            v-model="sortFilter"
            class="movie-franchise-page__sort"
            label="Сортировка"
            outlined
            dense
            emit-value
            map-options
            options-dense
            :options="sortOptions"
            :disable="store.isMoviesLoading"
            @update:model-value="onSortChange"
          >
            <template #prepend>
              <q-icon name="sort" />
            </template>
          </q-select>
        </div>

        <div v-if="store.isMoviesLoading && !store.franchiseMovies.length" class="movies-list">
          <MovieCardRowSkeleton v-for="index in 4" :key="index" />
        </div>

        <div v-else-if="store.franchiseMovies.length" class="movies-list">
          <MovieCardRow
            v-for="movie in store.franchiseMovies"
            :key="movie.id"
            :movie="movie"
            :show-folder-actions="false"
          />
        </div>

        <q-card v-else flat>
          <AppNoResultsPlug
            title="Во франшизе пока нет фильмов"
            body="Фильмы появятся здесь после добавления в Movie Manager."
          />
        </q-card>
      </section>
    </template>

    <q-card v-else flat>
      <AppNoResultsPlug
        title="Франшиза не найдена"
        body="Запись могла быть удалена или недоступна."
      />
    </q-card>
  </MoviesShell>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import { useMovieFranchiseStore } from 'src/stores/modules/movieFranchiseStore'
import { franchiseImageUrl } from 'src/api/mappers/Movie/movie.mapper'
import MoviesShell from 'src/components/client/Movies/MoviesShell.vue'
import MovieCardRow from 'src/components/client/Movies/MovieCardRow.vue'
import MovieCardRowSkeleton from 'src/components/client/Movies/MovieCardRowSkeleton.vue'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const SORT_ORDER_ASC = 'order'
const SORT_ORDER_DESC = '-order'
const SORT_YEAR_ASC = 'year'
const SORT_YEAR_DESC = '-year'

const props = defineProps<{
  id: string
}>()

const store = useMovieFranchiseStore()
const sortFilter = ref(SORT_ORDER_ASC)

const sortOptions = [
  { label: 'Порядок: по возрастанию', value: SORT_ORDER_ASC },
  { label: 'Порядок: по убыванию', value: SORT_ORDER_DESC },
  { label: 'Дата выхода: по возрастанию', value: SORT_YEAR_ASC },
  { label: 'Дата выхода: по убыванию', value: SORT_YEAR_DESC }
]

function resolvedSort(): string | null {
  return sortFilter.value === SORT_ORDER_ASC ? null : sortFilter.value
}

async function loadMovies(franchiseId: string): Promise<void> {
  await store.getFranchiseMovies(franchiseId, { sort: resolvedSort() })
}

function onSortChange(): void {
  if (!sortFilter.value) {
    sortFilter.value = SORT_ORDER_ASC
  }

  void loadMovies(props.id)
}

watch(
  () => props.id,
  async (id) => {
    await store.getFranchise(id)
    await loadMovies(id)
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.movie-franchise-page {
  &__skeleton,
  &__hero {
    display: grid;
    grid-template-columns: 180px minmax(0, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  &__poster,
  &__skeleton .movie-franchise-page__poster {
    width: 180px;
    max-width: 100%;
    aspect-ratio: 2 / 3;
    border-radius: 16px;
    overflow: hidden;
    background: rgba(40, 47, 83, 0.06);

    :deep(.q-img) {
      height: 100%;
    }
  }

  &__poster-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
    background:
      linear-gradient(135deg, rgba(108, 95, 252, 0.12), rgba(38, 166, 154, 0.08));
  }

  &__title {
    margin: 0 0 0.75rem;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__description {
    margin: 0;
    color: #282f53;
    font-size: 15px;
    line-height: 1.55;

    :deep(p) {
      margin: 0 0 0.5em;
    }

    :deep(p:last-child) {
      margin-bottom: 0;
    }

    :deep(ul),
    :deep(ol) {
      margin: 0.25em 0 0.5em;
      padding-left: 1.25em;
    }
  }

  &__movies-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 12px;
  }

  &__movies-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #282f53;
  }

  &__sort {
    flex: 0 1 280px;
    min-width: 220px;
  }
}

.movies-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

@media (max-width: 700px) {
  .movie-franchise-page__skeleton,
  .movie-franchise-page__hero {
    grid-template-columns: 1fr;
  }

  .movie-franchise-page__poster {
    width: 140px;
  }
}
</style>
