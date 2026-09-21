<template>
  <section v-if="groups.length" class="movie-crew">
    <div class="movie-crew__title">Создатели</div>
    <dl class="movie-crew__list">
      <div
        v-for="group in groups"
        :key="group.profession.id"
        class="movie-crew__row"
      >
        <dt class="movie-crew__profession">{{ professionLabel(group.profession) }}</dt>
        <dd class="movie-crew__people">
          <template
            v-for="(credit, index) in group.credits"
            :key="credit.id"
          >
            <MoviePersonLink :person="credit.person" :movie-id="movieId" />
            <span v-if="index < group.credits.length - 1">, </span>
          </template>
        </dd>
      </div>
    </dl>
  </section>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { groupMovieCredits, movieCrewCredits, professionLabel } from 'src/utils/movieCredits'
import { IMovieCredit } from 'src/types/Movie'
import MoviePersonLink from 'src/components/client/Movies/MoviePersonLink.vue'

const props = defineProps<{
  credits: IMovieCredit[]
  movieId?: string
}>()

const groups = computed(() => groupMovieCredits(movieCrewCredits(props.credits)))
</script>

<style lang="scss" scoped>
.movie-crew {
  margin-top: 1.5rem;

  &__title {
    margin-bottom: 12px;
    font-size: 18px;
    font-weight: 600;
    color: #282f53;
  }

  &__list {
    margin: 0;
  }

  &__row {
    display: grid;
    grid-template-columns: minmax(120px, 180px) 1fr;
    gap: 12px 16px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(40, 47, 83, 0.08);

    &:last-child {
      border-bottom: none;
    }
  }

  &__profession {
    color: #777a8f;
    font-size: 14px;
    font-weight: 400;
  }

  &__people {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    color: #282f53;
  }
}

@media (max-width: 599px) {
  .movie-crew__row {
    grid-template-columns: 1fr;
    gap: 4px;
  }
}
</style>
