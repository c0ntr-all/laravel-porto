<template>
  <aside v-if="actors.length" class="movie-actors">
    <div class="movie-actors__title">В главных ролях</div>
    <ul class="movie-actors__list">
      <li
        v-for="credit in preview"
        :key="credit.id"
        class="movie-actors__item"
      >
        <MoviePersonLink :person="credit.person" :movie-id="movieId" />
        <span v-if="credit.description" class="movie-actors__role">
          {{ credit.description }}
        </span>
      </li>
    </ul>
    <router-link
      class="movie-actors__all"
      :to="{
        name: 'movie-persons',
        params: { id: movieId },
        hash: actorsCount > MOVIE_ACTORS_SIDEBAR_LIMIT ? '#actors' : undefined
      }"
    >
      Все актёры ({{ actorsCount }})
    </router-link>
  </aside>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { MOVIE_ACTORS_SIDEBAR_LIMIT } from 'src/enums/Movie/MovieProfession'
import { movieActorCredits } from 'src/utils/movieCredits'
import { IMovieCredit } from 'src/types/Movie'
import MoviePersonLink from 'src/components/client/Movies/MoviePersonLink.vue'

const props = defineProps<{
  movieId: string
  credits: IMovieCredit[]
  actorsCount: number
}>()

const actors = computed(() => movieActorCredits(props.credits))
const preview = computed(() => actors.value.slice(0, MOVIE_ACTORS_SIDEBAR_LIMIT))
const actorsCount = computed(() => Math.max(props.actorsCount, actors.value.length))
</script>

<style lang="scss" scoped>
.movie-actors {
  min-width: 0;

  &__title {
    margin-bottom: 12px;
    font-size: 18px;
    font-weight: 600;
    color: #282f53;
  }

  &__list {
    margin: 0 0 12px;
    padding: 0;
    list-style: none;
  }

  &__item {
    margin-bottom: 8px;
    font-size: 14px;
    line-height: 1.4;
  }

  &__role {
    display: block;
    margin-top: 2px;
    color: #777a8f;
    font-size: 13px;
  }

  &__all {
    font-size: 14px;
    font-weight: 700;
    color: #282f53;
    text-decoration: none;

    &:hover {
      color: $primary;
    }
  }
}
</style>
