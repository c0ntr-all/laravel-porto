<template>
  <div class="q-mb-md">
    <AppBackButton :link="`/movies/${id}`" text="К фильму" />
  </div>

  <div v-if="movieStore.isMovieLoading && !movieStore.movie">
    <q-skeleton type="text" width="40%" />
    <q-skeleton type="text" width="70%" class="q-mt-md" />
    <q-skeleton type="text" width="55%" />
  </div>

  <template v-else-if="movieStore.movie">
    <h1 class="persons-page__title">
      {{ movieStore.movie.title }}
    </h1>
    <div class="persons-page__caption">Создатели и актёры</div>

    <section
      v-for="group in groups"
      :id="professionAnchor(group.profession.en_name)"
      :key="group.profession.id"
      class="persons-group"
    >
      <h2 class="persons-group__title">{{ professionLabel(group.profession) }}</h2>
      <ul class="persons-group__list">
        <li
          v-for="credit in group.credits"
          :key="credit.id"
          class="persons-group__item"
        >
          <MoviePersonLink :person="credit.person" :movie-id="id" />
          <span v-if="credit.description" class="persons-group__role">
            {{ credit.description }}
          </span>
        </li>
      </ul>
    </section>

    <q-card v-if="!groups.length" class="q-mb-md" flat>
      <AppNoResultsPlug
        title="Персоны не найдены"
        body="У этого фильма пока нет связанных персон."
      />
    </q-card>
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
import { groupMovieCredits, professionLabel } from 'src/utils/movieCredits'
import { MOVIE_ACTOR_PROFESSION } from 'src/enums/Movie/MovieProfession'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import MoviePersonLink from 'src/components/client/Movies/MoviePersonLink.vue'

const props = defineProps<{
  id: string
}>()

const movieStore = useMovieStore()
const groups = computed(() => groupMovieCredits(movieStore.movie?.credits ?? []))

function professionAnchor(enName: string): string {
  return enName === MOVIE_ACTOR_PROFESSION ? 'actors' : enName
}

watch(
  () => props.id,
  (id) => {
    void movieStore.getMovie(id)
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.persons-page {
  &__title {
    margin: 0 0 4px;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__caption {
    margin-bottom: 1.5rem;
    color: #777a8f;
    font-size: 15px;
  }
}

.persons-group {
  margin-bottom: 1.75rem;

  &__title {
    margin: 0 0 12px;
    font-size: 18px;
    font-weight: 600;
    color: #282f53;
  }

  &__list {
    margin: 0;
    padding: 0;
    list-style: none;
    column-count: 2;
    column-gap: 2rem;
  }

  &__item {
    break-inside: avoid;
    margin-bottom: 10px;
    font-size: 15px;
    line-height: 1.4;
  }

  &__role {
    display: block;
    color: #777a8f;
    font-size: 13px;
  }
}

@media (max-width: 700px) {
  .persons-group__list {
    column-count: 1;
  }
}
</style>
