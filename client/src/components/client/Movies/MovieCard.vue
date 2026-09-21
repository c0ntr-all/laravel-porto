<template>
  <router-link
    class="movie-card"
    :to="{ name: 'movie', params: { id: movie.id } }"
  >
    <div class="movie-card__poster">
      <q-img
        v-if="poster"
        :src="poster"
        :alt="movie.title"
        class="movie-card__image"
        fit="cover"
      >
        <template #error>
          <div class="movie-card__placeholder">
            <q-icon name="movie" size="42px" />
          </div>
        </template>
      </q-img>
      <div v-else class="movie-card__placeholder">
        <q-icon name="movie" size="42px" />
      </div>

      <div class="movie-card__type">{{ typeLabel }}</div>

      <div v-if="ratingLabel" class="movie-card__rating">
        <q-icon name="star" size="14px" />
        {{ ratingLabel }}
      </div>

      <MovieFolderActions variant="overlay" :movie="movie" />
    </div>

    <div class="movie-card__meta">
      <div class="movie-card__title" :title="movie.title">{{ movie.title }}</div>
      <div class="movie-card__subtitle">
        <span v-if="movie.year">{{ movie.year }}</span>
        <span v-if="countriesLabel" class="movie-card__dot">·</span>
        <span v-if="countriesLabel" :title="countriesLabel">{{ countriesLabel }}</span>
      </div>
      <div v-if="movie.short_description" class="movie-card__description" :title="movie.short_description">
        {{ movie.short_description }}
      </div>
      <div v-if="visibleGenres.length" class="movie-card__genres">
        <q-chip
          v-for="genre in visibleGenres"
          :key="genre.id"
          size="sm"
          dense
          outline
          color="primary"
          text-color="primary"
        >
          {{ genre.name }}
        </q-chip>
        <q-chip
          v-if="hiddenGenresCount"
          size="sm"
          dense
          color="grey-3"
          text-color="grey-8"
        >
          +{{ hiddenGenresCount }}
        </q-chip>
      </div>
    </div>
  </router-link>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IMovie } from 'src/types/Movie'
import { MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'
import MovieFolderActions from 'src/components/client/Movies/MovieFolderActions.vue'

const props = defineProps<{
  movie: IMovie
}>()

const poster = computed(() => moviePosterUrl(props.movie))
const typeLabel = computed(() => MOVIE_TYPE_LABELS[props.movie.type])
const ratingLabel = computed(() => {
  if (props.movie.kp_rating == null) {
    return ''
  }

  return props.movie.kp_rating.toFixed(1)
})
const visibleGenres = computed(() => props.movie.genres.slice(0, 2))
const hiddenGenresCount = computed(() => Math.max(props.movie.genres.length - 2, 0))
const countriesLabel = computed(() => (
  props.movie.countries.map(country => country.name).filter(Boolean).join(', ')
))
</script>

<style lang="scss" scoped>
.movie-card {
  display: block;
  color: inherit;
  text-decoration: none;
  min-width: 0;

  &:hover {
    .movie-card__poster {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(40, 47, 83, 0.16);
    }

    .movie-card__image {
      transform: scale(1.04);
    }

    .movie-card__title {
      color: $primary;
    }
  }

  @media (hover: hover) {
    .movie-folder-actions {
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.15s ease;
    }

    &:hover .movie-folder-actions {
      opacity: 1;
      pointer-events: auto;
    }
  }

  &__poster {
    position: relative;
    overflow: hidden;
    aspect-ratio: 2 / 3;
    border-radius: 16px;
    background: #ececf4;
    box-shadow: 0 6px 16px rgba(40, 47, 83, 0.08);
    margin-bottom: 0.75rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  &__image {
    height: 100%;
    transition: transform 0.35s ease;

    :deep(.q-img__image) {
      transition: transform 0.35s ease;
    }
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
    background:
      linear-gradient(135deg, rgba(108, 95, 252, 0.12), rgba(38, 166, 154, 0.08));
  }

  &__type,
  &__rating {
    position: absolute;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 999px;
    color: #fff;
    font-size: 12px;
    line-height: 1;
    backdrop-filter: blur(6px);
  }

  &__type {
    top: 10px;
    left: 10px;
    background: rgba(108, 95, 252, 0.9);
    font-weight: 600;
    letter-spacing: 0.02em;
  }

  &__rating {
    top: 10px;
    right: 10px;
    background: rgba(18, 18, 18, 0.72);
  }

  &__title {
    margin-bottom: 2px;
    font-size: 15px;
    font-weight: 600;
    color: #282f53;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.15s ease;
  }

  &__subtitle {
    font-size: 13px;
    line-height: 1.35;
    color: #777a8f;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 6px;
  }

  &__description {
    font-size: 13px;
    line-height: 1.4;
    color: #777a8f;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    line-clamp: 3;
    overflow: hidden;
    margin-bottom: 6px;
  }

  &__dot {
    margin: 0 4px;
  }

  &__genres {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
  }
}
</style>
