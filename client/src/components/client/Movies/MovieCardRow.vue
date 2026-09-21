<template>
  <div class="movie-card-row">
    <router-link
      class="movie-card-row__main"
      :to="{ name: 'movie', params: { id: movie.id } }"
    >
      <div class="movie-card-row__poster">
        <q-img
          v-if="poster"
          :src="poster"
          :alt="movie.title"
          class="movie-card-row__image"
          fit="cover"
        >
          <template #error>
            <div class="movie-card-row__placeholder">
              <q-icon name="movie" size="28px" />
            </div>
          </template>
        </q-img>
        <div v-else class="movie-card-row__placeholder">
          <q-icon name="movie" size="28px" />
        </div>
      </div>

      <div class="movie-card-row__body">
        <div class="movie-card-row__head">
          <div class="movie-card-row__title" :title="movie.title">{{ movie.title }}</div>
          <div v-if="ratingLabel" class="movie-card-row__rating">
            <q-icon name="star" size="16px" color="amber" />
            {{ ratingLabel }}
          </div>
        </div>

        <div class="movie-card-row__subtitle">
          <q-chip color="primary" text-color="white" size="sm" dense>
            {{ typeLabel }}
          </q-chip>
          <span v-if="movie.year">{{ movie.year }}</span>
          <span v-if="countriesLabel" class="movie-card-row__dot">·</span>
          <span v-if="countriesLabel" :title="countriesLabel">{{ countriesLabel }}</span>
        </div>

        <div v-if="movie.short_description" class="movie-card-row__description">
          {{ movie.short_description }}
        </div>

        <div v-if="movie.genres.length" class="movie-card-row__genres">
          <q-chip
            v-for="genre in movie.genres"
            :key="genre.id"
            size="sm"
            dense
            outline
            color="primary"
            text-color="primary"
          >
            {{ genre.name }}
          </q-chip>
        </div>
      </div>
    </router-link>

    <MovieFolderActions variant="row" :movie="movie" />
  </div>
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
const countriesLabel = computed(() => (
  props.movie.countries.map(country => country.name).filter(Boolean).join(', ')
))
</script>

<style lang="scss" scoped>
.movie-card-row {
  display: flex;
  align-items: stretch;
  gap: 1rem;
  min-width: 0;
  padding: 12px;
  border-radius: 16px;
  background: #fff;
  border: 1px solid rgba(40, 47, 83, 0.08);
  box-shadow: 0 6px 16px rgba(40, 47, 83, 0.06);
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(40, 47, 83, 0.14);

    .movie-card-row__image {
      transform: scale(1.04);
    }

    .movie-card-row__title {
      color: $primary;
    }
  }

  &__main {
    display: flex;
    align-items: stretch;
    gap: 1rem;
    min-width: 0;
    flex: 1;
    color: inherit;
    text-decoration: none;
  }

  &__poster {
    position: relative;
    flex: 0 0 92px;
    width: 92px;
    overflow: hidden;
    aspect-ratio: 2 / 3;
    border-radius: 12px;
    background: #ececf4;
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

  &__body {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
    padding: 2px 0;
  }

  &__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 6px;
  }

  &__title {
    min-width: 0;
    font-size: 17px;
    font-weight: 600;
    line-height: 1.3;
    color: #282f53;
    transition: color 0.15s ease;
  }

  &__rating {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
    font-size: 14px;
    font-weight: 600;
    color: #282f53;
  }

  &__subtitle {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 13px;
    color: #777a8f;
  }

  &__dot {
    margin: 0;
  }

  &__description {
    font-size: 14px;
    line-height: 1.45;
    color: #55586d;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    overflow: hidden;
    margin-bottom: 8px;
  }

  &__genres {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: auto;
  }
}

@media (max-width: $breakpoint-xs-max) {
  .movie-card-row {
    &__poster {
      flex-basis: 72px;
      width: 72px;
    }

    &__title {
      font-size: 15px;
    }
  }
}
</style>
