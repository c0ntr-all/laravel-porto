<template>
  <MoviesShell>
    <div class="movie-franchises-page">
      <h1 class="movie-franchises-page__title">Франшизы</h1>
      <p class="movie-franchises-page__caption">
        Общие вселенные и связанные фильмы
      </p>

      <div v-if="store.isLoading && !store.franchises.length" class="movies-grid">
        <q-skeleton
          v-for="index in 6"
          :key="index"
          class="movie-franchises-page__skeleton"
          square
        />
      </div>

      <div v-else-if="store.franchises.length" class="movies-grid">
        <MovieFranchiseCard
          v-for="franchise in store.franchises"
          :key="franchise.id"
          :franchise="franchise"
        />
      </div>

      <q-card v-else flat>
        <AppNoResultsPlug
          title="Франшиз пока нет"
          body="Когда появятся франшизы, они отобразятся здесь."
        />
      </q-card>
    </div>
  </MoviesShell>
</template>

<script lang="ts" setup>
import { onMounted } from 'vue'
import { useMovieFranchiseStore } from 'src/stores/modules/movieFranchiseStore'
import MoviesShell from 'src/components/client/Movies/MoviesShell.vue'
import MovieFranchiseCard from 'src/components/client/Movies/MovieFranchiseCard.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const store = useMovieFranchiseStore()

onMounted(() => {
  void store.getFranchises()
})
</script>

<style lang="scss" scoped>
.movie-franchises-page {
  &__title {
    margin: 0 0 4px;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__caption {
    margin: 0 0 1.5rem;
    color: #777a8f;
    font-size: 15px;
  }

  &__skeleton {
    aspect-ratio: 2 / 3;
    border-radius: 16px;
  }
}

.movies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 1.5rem 1.25rem;
}
</style>
