<template>
  <div class="q-mb-md">
    <AppBackButton link="/movies" text="К фильмам" />
  </div>

  <div v-if="movieStore.isMovieLoading && !movieStore.movie" class="movie-page-skeleton">
    <q-skeleton class="movie-page-skeleton__poster" square />
    <div class="movie-page-skeleton__info">
      <q-skeleton type="text" width="60%" />
      <q-skeleton type="text" width="30%" />
      <q-skeleton type="text" width="90%" />
      <q-skeleton type="text" width="80%" />
    </div>
    <div class="movie-page-skeleton__aside">
      <q-skeleton type="text" width="70%" />
      <q-skeleton type="text" width="90%" />
      <q-skeleton type="text" width="80%" />
    </div>
  </div>

  <template v-else-if="movieStore.movie">
    <div class="movie-page" :class="{ 'movie-page--no-aside': !hasActors }">
      <div class="movie-page__poster">
        <q-img
          v-if="poster"
          :src="poster"
          :alt="movieStore.movie.title"
          class="movie-page__image"
          fit="cover"
        >
          <template #error>
            <div class="movie-page__placeholder">
              <q-icon name="movie" size="48px" />
            </div>
          </template>
        </q-img>
        <div v-else class="movie-page__placeholder">
          <q-icon name="movie" size="48px" />
        </div>
      </div>

      <div class="movie-page__main">
        <h1 class="movie-page__title">{{ movieStore.movie.title }}</h1>
        <div class="movie-page__meta">
          <q-chip color="primary" text-color="white" dense>
            {{ typeLabel }}
          </q-chip>
          <span v-if="movieStore.movie.year">{{ movieStore.movie.year }}</span>
          <span v-if="ratingLabel" class="movie-page__rating">
            <q-icon name="star" size="18px" color="amber" />
            {{ ratingLabel }}
          </span>
        </div>
        <div v-if="countriesLabel" class="movie-page__line">
          {{ countriesLabel }}
        </div>
        <div v-if="movieStore.movie.genres.length" class="movie-page__genres">
          <q-chip
            v-for="genre in movieStore.movie.genres"
            :key="genre.id"
            outline
            color="primary"
            text-color="primary"
            dense
          >
            {{ genre.name }}
          </q-chip>
        </div>
        <q-btn
          v-if="kinopoiskUrl"
          class="movie-page__kinopoisk"
          unelevated
          no-caps
          color="primary"
          icon="open_in_new"
          label="Перейти на Кинопоиск"
          :href="kinopoiskUrl"
          target="_blank"
          rel="noopener noreferrer"
        />

        <div class="movie-page__description">
          <p v-if="movieStore.movie.description">{{ movieStore.movie.description }}</p>
          <p v-else class="text-grey-5">Описание отсутствует</p>
        </div>

        <MovieCrewSection :credits="movieStore.movie.credits" :movie-id="movieStore.movie.id" />
      </div>

      <MovieActorsSidebar
        v-if="hasActors"
        class="movie-page__aside"
        :movie-id="movieStore.movie.id"
        :credits="movieStore.movie.credits"
      />
    </div>
  </template>

  <q-card v-else class="q-mb-md" flat>
    <AppNoResultsPlug
      title="Фильм не найден"
      body="Запись могла быть удалена или недоступна."
    />
  </q-card>
</template>

<script lang="ts" setup>
import { computed, watch } from 'vue'
import { useMovieStore } from 'src/stores/modules/movieStore'
import { MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'
import { movieActorCredits } from 'src/utils/movieCredits'
import { kinopoiskMovieUrl } from 'src/utils/kinopoisk'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import MovieActorsSidebar from 'src/components/client/Movies/MovieActorsSidebar.vue'
import MovieCrewSection from 'src/components/client/Movies/MovieCrewSection.vue'

const props = defineProps<{
  id: string
}>()

const movieStore = useMovieStore()

const poster = computed(() => (
  movieStore.movie ? moviePosterUrl(movieStore.movie) : null
))
const typeLabel = computed(() => (
  movieStore.movie ? MOVIE_TYPE_LABELS[movieStore.movie.type] : ''
))
const ratingLabel = computed(() => {
  if (movieStore.movie?.kp_rating == null) {
    return ''
  }

  return movieStore.movie.kp_rating.toFixed(1)
})
const countriesLabel = computed(() => (
  movieStore.movie?.countries.map(country => country.name).filter(Boolean).join(', ') ?? ''
))
const hasActors = computed(() => movieActorCredits(movieStore.movie?.credits ?? []).length > 0)
const kinopoiskUrl = computed(() => (
  movieStore.movie
    ? kinopoiskMovieUrl(movieStore.movie.kp_id, movieStore.movie.type)
    : null
))

watch(
  () => props.id,
  (id) => {
    void movieStore.getMovie(id)
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.movie-page-skeleton,
.movie-page {
  display: grid;
  grid-template-columns: 220px minmax(0, 1fr) minmax(220px, 280px);
  gap: 2rem;
  align-items: start;
}

.movie-page-skeleton__poster,
.movie-page__poster {
  width: 220px;
  max-width: 100%;
  aspect-ratio: 2 / 3;
  border-radius: 16px;
  overflow: hidden;
}

.movie-page {
  &__image,
  &__placeholder {
    height: 100%;
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
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

  &__meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 0.75rem;
    color: #777a8f;
    font-size: 15px;
  }

  &__rating {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #282f53;
    font-weight: 600;
  }

  &__line {
    margin-bottom: 0.75rem;
    color: #777a8f;
  }

  &__genres {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 1rem;
  }

  &__kinopoisk {
    margin-bottom: 1rem;
  }

  &__description {
    margin: 0;
    color: #282f53;
    font-size: 15px;
    line-height: 1.55;
    white-space: pre-line;

    p {
      margin: 0;
    }
  }

  &__aside {
    padding-left: 8px;
    border-left: 1px solid rgba(40, 47, 83, 0.08);
  }

  &--no-aside {
    grid-template-columns: 220px minmax(0, 1fr);
  }
}

@media (max-width: 1100px) {
  .movie-page-skeleton,
  .movie-page {
    grid-template-columns: 200px minmax(0, 1fr);
  }

  .movie-page__aside,
  .movie-page-skeleton__aside {
    grid-column: 1 / -1;
    border-left: none;
    padding-left: 0;
    padding-top: 8px;
    border-top: 1px solid rgba(40, 47, 83, 0.08);
  }
}

@media (max-width: 700px) {
  .movie-page-skeleton,
  .movie-page {
    grid-template-columns: 1fr;
  }

  .movie-page-skeleton__poster,
  .movie-page__poster {
    width: 180px;
  }
}
</style>
