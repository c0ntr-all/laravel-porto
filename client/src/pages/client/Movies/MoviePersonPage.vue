<template>
  <div class="q-mb-md">
    <AppBackButton :link="backLink" :text="backText" />
  </div>

  <div v-if="personStore.isPersonLoading && !personStore.person" class="person-page-skeleton">
    <q-skeleton class="person-page-skeleton__photo" square />
    <div>
      <q-skeleton type="text" width="50%" />
      <q-skeleton type="text" width="30%" />
      <q-skeleton type="text" width="70%" />
    </div>
  </div>

  <template v-else-if="personStore.person">
    <div class="person-head">
      <div class="person-head__photo">
        <q-img
          v-if="personStore.person.photo"
          :src="personStore.person.photo"
          :alt="personStore.person.name"
          class="person-head__image"
          fit="cover"
        >
          <template #error>
            <div class="person-head__placeholder">
              <q-icon name="person" size="48px" />
            </div>
          </template>
        </q-img>
        <div v-else class="person-head__placeholder">
          <q-icon name="person" size="48px" />
        </div>
      </div>

      <div class="person-head__info">
        <h1 class="person-head__name">{{ personStore.person.name }}</h1>
        <div v-if="personStore.person.en_name" class="person-head__en-name">
          {{ personStore.person.en_name }}
        </div>
        <div v-if="professionsLabel" class="person-head__professions">
          {{ professionsLabel }}
        </div>
      </div>
    </div>

    <section v-if="personStore.person.movies.length" class="person-movies">
      <h2 class="person-movies__title">Фильмография</h2>
      <ul class="person-movies__list">
        <li
          v-for="movie in personStore.person.movies"
          :key="movie.id"
          class="person-movies__item"
        >
          <span class="person-movies__year">{{ movie.year || '—' }}</span>
          <router-link
            class="person-movies__link"
            :to="{ name: 'movie', params: { id: movie.id } }"
          >
            {{ movie.title }}
          </router-link>
        </li>
      </ul>
    </section>
  </template>

  <q-card v-else class="q-mb-md" flat>
    <AppNoResultsPlug
      title="Персона не найдена"
      body="Запись могла быть удалена или недоступна."
    />
  </q-card>
</template>

<script lang="ts" setup>
import { computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useMoviePersonStore } from 'src/stores/modules/moviePersonStore'
import { professionLabel } from 'src/utils/movieCredits'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  id: string
}>()

const route = useRoute()
const personStore = useMoviePersonStore()

const backLink = computed(() => {
  const movieId = route.query.movie

  return typeof movieId === 'string' && movieId
    ? `/movies/${movieId}`
    : '/movies'
})

const backText = computed(() => (
  route.query.movie ? 'К фильму' : 'К фильмам'
))

const professionsLabel = computed(() => {
  const person = personStore.person

  if (!person) {
    return ''
  }

  const professions = person.professions.length
    ? person.professions
    : (person.profession ? [person.profession] : [])

  return professions.map(professionLabel).filter(Boolean).join(', ')
})

watch(
  () => props.id,
  (id) => {
    void personStore.getPerson(id)
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.person-page-skeleton,
.person-head {
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.person-page-skeleton__photo,
.person-head__photo {
  width: 180px;
  max-width: 100%;
  aspect-ratio: 2 / 3;
  border-radius: 16px;
  overflow: hidden;
  flex-shrink: 0;
}

.person-head {
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

  &__name {
    margin: 0 0 0.5rem;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__en-name {
    margin-bottom: 0.75rem;
    color: #777a8f;
    font-size: 16px;
  }

  &__professions {
    color: #282f53;
    font-size: 15px;
  }
}

.person-movies {
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
  }

  &__item {
    display: grid;
    grid-template-columns: 64px 1fr;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(40, 47, 83, 0.08);
    font-size: 15px;
  }

  &__year {
    color: #777a8f;
  }

  &__link {
    color: $primary;
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }
}
</style>
