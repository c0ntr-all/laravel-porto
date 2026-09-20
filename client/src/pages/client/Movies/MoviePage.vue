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
  </div>

  <template v-else-if="movieStore.movie">
    <div class="movie-head">
      <div class="movie-head__poster">
        <q-img
          v-if="poster"
          :src="poster"
          :alt="movieStore.movie.title"
          class="movie-head__image"
          fit="cover"
        >
          <template #error>
            <div class="movie-head__placeholder">
              <q-icon name="movie" size="48px" />
            </div>
          </template>
        </q-img>
        <div v-else class="movie-head__placeholder">
          <q-icon name="movie" size="48px" />
        </div>
      </div>

      <div class="movie-head__info">
        <h1 class="movie-head__title">{{ movieStore.movie.title }}</h1>
        <div class="movie-head__meta">
          <q-chip color="primary" text-color="white" dense>
            {{ typeLabel }}
          </q-chip>
          <span v-if="movieStore.movie.year">{{ movieStore.movie.year }}</span>
          <span v-if="ratingLabel" class="movie-head__rating">
            <q-icon name="star" size="18px" color="amber" />
            {{ ratingLabel }}
          </span>
        </div>
        <div v-if="countriesLabel" class="movie-head__line">
          {{ countriesLabel }}
        </div>
        <div class="movie-head__description">
          <p v-if="movieStore.movie.description">{{ movieStore.movie.description }}</p>
          <p v-else class="text-grey-5">Описание отсутствует</p>
        </div>
        <div v-if="movieStore.movie.genres.length" class="movie-head__genres">
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
      </div>
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
import AppBackButton from 'src/components/default/AppBackButton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

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
.movie-head {
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
  flex-wrap: wrap;
}

.movie-page-skeleton__poster,
.movie-head__poster {
  width: 220px;
  max-width: 100%;
  aspect-ratio: 2 / 3;
  border-radius: 16px;
  overflow: hidden;
  flex-shrink: 0;
}

.movie-page-skeleton__info,
.movie-head__info {
  flex: 1 1 280px;
  min-width: 0;
}

.movie-head {
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

  &__description {
    margin: 0 0 1rem;
    color: #282f53;
    font-size: 15px;
    line-height: 1.55;
    white-space: pre-line;

    p {
      margin: 0;
    }
  }

  &__genres {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }
}
</style>
