<template>
  <component
    :is="linkTag"
    class="movie-card-h"
    v-bind="linkProps"
  >
    <div class="movie-card-h__poster">
      <q-img
        v-if="poster"
        :src="poster"
        :alt="movie.title"
        class="movie-card-h__image"
        fit="cover"
      >
        <template #error>
          <div class="movie-card-h__placeholder">
            <q-icon name="movie" size="28px" />
          </div>
        </template>
      </q-img>
      <div v-else class="movie-card-h__placeholder">
        <q-icon name="movie" size="28px" />
      </div>

      <div v-if="ratingLabel" class="movie-card-h__rating">
        <q-icon name="star" size="12px" />
        {{ ratingLabel }}
      </div>
    </div>

    <div class="movie-card-h__meta">
      <div class="movie-card-h__title" :title="movie.title">{{ movie.title }}</div>
      <div v-if="movie.genres.length" class="movie-card-h__genres">
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
  </component>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { IMovie } from 'src/types/Movie'
import { moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'

const props = defineProps<{
  movie: IMovie
}>()

const poster = computed(() => moviePosterUrl(props.movie))

const ratingLabel = computed(() => {
  if (props.movie.kp_rating == null) {
    return ''
  }

  return props.movie.kp_rating.toFixed(1)
})

const visibleGenres = computed(() => props.movie.genres.slice(0, 4))
const hiddenGenresCount = computed(() => Math.max(props.movie.genres.length - 4, 0))

const isNavigable = computed(() => /^\d+$/.test(props.movie.id))

const linkTag = computed(() => (isNavigable.value ? RouterLink : 'div'))

const linkProps = computed(() => {
  if (!isNavigable.value) {
    return {}
  }

  return {
    to: { name: 'movie', params: { id: props.movie.id } }
  }
})
</script>

<style lang="scss" scoped>
.movie-card-h {
  display: flex;
  align-items: stretch;
  gap: 14px;
  min-width: 0;
  color: inherit;
  text-decoration: none;
  padding: 10px;
  border-radius: 12px;
  border: 1px solid #e8ebf0;
  background: #fafbfc;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;

  &:hover {
    border-color: rgba(108, 95, 252, 0.35);
    box-shadow: 0 4px 14px rgba(40, 47, 83, 0.08);

    .movie-card-h__title {
      color: $primary;
    }

    .movie-card-h__image {
      transform: scale(1.03);
    }
  }

  &__poster {
    position: relative;
    flex-shrink: 0;
    width: 72px;
    aspect-ratio: 2 / 3;
    overflow: hidden;
    border-radius: 10px;
    background: #ececf4;
    box-shadow: 0 4px 10px rgba(40, 47, 83, 0.1);
  }

  &__image {
    width: 100%;
    height: 100%;
    transition: transform 0.3s ease;

    :deep(.q-img__content) {
      height: 100%;
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

  &__rating {
    position: absolute;
    right: 4px;
    bottom: 4px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 2px 6px;
    border-radius: 999px;
    color: #fff;
    font-size: 10px;
    line-height: 1;
    background: rgba(18, 18, 18, 0.72);
    backdrop-filter: blur(4px);
  }

  &__meta {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    padding: 2px 0;
  }

  &__title {
    font-size: 15px;
    font-weight: 600;
    line-height: 1.35;
    color: #282f53;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    overflow: hidden;
    transition: color 0.15s ease;
  }

  &__genres {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
  }
}
</style>
