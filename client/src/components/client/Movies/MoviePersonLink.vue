<template>
  <router-link
    class="movie-person-link"
    :to="{
      name: 'movie-person',
      params: { id: person.id },
      query: movieId ? { movie: movieId } : {}
    }"
  >
    {{ person.name }}
  </router-link>
</template>

<script lang="ts" setup>
import { IMovieCredit } from 'src/types/Movie'

defineProps<{
  person: IMovieCredit['person']
  movieId?: string
}>()
</script>

<style lang="scss" scoped>
.movie-person-link {
  color: #282f53;
  text-decoration: none;

  &:hover {
    color: $primary;
  }
}
</style>
