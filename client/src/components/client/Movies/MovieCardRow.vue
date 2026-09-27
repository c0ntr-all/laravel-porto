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
          <div
            v-if="ratingLabel"
            class="movie-card-row__rating"
            :class="`movie-card-row__rating--${ratingTone}`"
          >
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

    <div class="movie-card-row__aside">
      <div></div>
      <MovieFolderActions variant="row" :movie="movie" />
      <div v-if="addedAtLabel" class="movie-card-row__added">
        {{ addedAtLabel }}
      </div>
      <div v-else></div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IMovie } from 'src/types/Movie'
import { MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'
import { humanDatetime } from 'src/utils/datetime'
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
const ratingTone = computed(() => {
  const rating = Math.floor(props.movie.kp_rating ?? 0)

  if (rating >= 7) {
    return 'high'
  }

  if (rating >= 5) {
    return 'mid'
  }

  return 'low'
})
const countriesLabel = computed(() => (
  props.movie.countries.map(country => country.name).filter(Boolean).join(', ')
))
const addedAtLabel = computed(() => (
  props.movie.added_at ? humanDatetime(props.movie.added_at) : ''
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
  transition: background-color 0.15s ease;

  &:hover {
    background: rgba(242, 242, 242, 0.6);
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
  }

  &__rating {
    flex-shrink: 0;
    font-size: 18px;
    font-weight: 600;
    line-height: 1;

    &--low {
      color: #e53935;
    }

    &--mid {
      color: #9e9e9e;
    }

    &--high {
      color: #43a047;
    }
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

  &__aside {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: space-between;
    flex-shrink: 0;
    gap: 8px;
    min-width: 0;
  }

  &__added {
    font-size: 12px;
    line-height: 1.3;
    color: #9aa0b8;
    white-space: nowrap;
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
