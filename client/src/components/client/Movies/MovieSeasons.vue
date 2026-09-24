<template>
  <section v-if="seasons.length" class="movie-seasons">
    <div class="movie-seasons__title">Сезоны</div>

    <q-list class="movie-seasons__list" separator>
      <q-expansion-item
        v-for="season in seasons"
        :key="season.id"
        class="movie-season"
        expand-separator
        header-class="movie-season__header"
      >
        <template #header>
          <div class="movie-season__head">
            <div class="movie-season__poster">
              <q-img
                v-if="seasonPosterUrl(season)"
                :src="seasonPosterUrl(season)"
                :alt="seasonTitle(season)"
                fit="cover"
              >
                <template #error>
                  <div class="movie-season__poster-fallback">
                    <q-icon name="live_tv" />
                  </div>
                </template>
              </q-img>
              <div v-else class="movie-season__poster-fallback">
                <q-icon name="live_tv" />
              </div>
            </div>

            <div class="movie-season__info">
              <div class="movie-season__name">{{ seasonTitle(season) }}</div>
              <div class="movie-season__meta">
                <span>{{ seasonEpisodesLabel(season) }}</span>
                <span v-if="season.air_date">{{ season.air_date }}</span>
              </div>
            </div>

            <q-btn
              class="movie-season__watch"
              :class="{ 'movie-season__watch--active': season.is_watched }"
              round
              dense
              unelevated
              type="button"
              :icon="season.is_watched ? 'check_circle' : 'check_circle_outline'"
              :loading="movieStore.isWatchPending(`season:${season.id}`)"
              @click.stop="movieStore.toggleSeasonWatched(season)"
            >
              <q-tooltip>
                {{ season.is_watched ? 'Снять отметку сезона' : 'Отметить сезон просмотренным' }}
              </q-tooltip>
            </q-btn>
          </div>
        </template>

        <div class="movie-season__episodes">
          <div
            v-for="episode in season.episodes"
            :key="episode.id"
            class="movie-episode"
            :class="{ 'movie-episode--open': openedEpisodeId === episode.id }"
          >
            <div
              class="movie-episode__row"
              :class="{ 'movie-episode__row--expandable': Boolean(episode.description) }"
              @click="toggleEpisode(episode)"
            >
              <div class="movie-episode__still">
                <q-img
                  v-if="episodeStillUrl(episode)"
                  :src="episodeStillUrl(episode)"
                  :alt="episodeTitle(episode)"
                  fit="cover"
                >
                  <template #error>
                    <div class="movie-episode__still-fallback">
                      <q-icon name="movie" />
                    </div>
                  </template>
                </q-img>
                <div v-else class="movie-episode__still-fallback">
                  <q-icon name="movie" />
                </div>
              </div>

              <div class="movie-episode__info">
                <div class="movie-episode__name">{{ episodeTitle(episode) }}</div>
                <div v-if="episode.air_date" class="movie-episode__date">{{ episode.air_date }}</div>
              </div>

              <q-icon
                v-if="episode.description"
                class="movie-episode__chevron"
                :name="openedEpisodeId === episode.id ? 'expand_less' : 'expand_more'"
              />

              <q-btn
                class="movie-episode__watch"
                :class="{ 'movie-episode__watch--active': episode.is_watched }"
                round
                dense
                unelevated
                type="button"
                :icon="episode.is_watched ? 'check_circle' : 'check_circle_outline'"
                :loading="movieStore.isWatchPending(`episode:${episode.id}`)"
                @click.stop="movieStore.toggleEpisodeWatched(season, episode.id)"
              >
                <q-tooltip>
                  {{ episode.is_watched ? 'Снять отметку серии' : 'Отметить серию просмотренной' }}
                </q-tooltip>
              </q-btn>
            </div>

            <div v-if="openedEpisodeId === episode.id && episode.description" class="movie-episode__description">
              {{ episode.description }}
            </div>
          </div>
        </div>
      </q-expansion-item>
    </q-list>
  </section>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useMovieStore } from 'src/stores/modules/movieStore'
import {
  episodeStillUrl,
  seasonPosterUrl
} from 'src/api/mappers/Movie/movie.mapper'
import { seasonEpisodesTotal, seasonWatchedCount } from 'src/utils/movieSeasons'
import { IMovieEpisode, IMovieSeason } from 'src/types/Movie'

const movieStore = useMovieStore()
const openedEpisodeId = ref<string | null>(null)

const seasons = computed(() => movieStore.movie?.seasons ?? [])

function seasonTitle(season: IMovieSeason): string {
  return season.name?.trim() || `Сезон ${season.number}`
}

function seasonEpisodesLabel(season: IMovieSeason): string {
  const total = seasonEpisodesTotal(season)
  const watched = seasonWatchedCount(season)

  if (!total) {
    return 'Нет серий'
  }

  return `${watched} из ${total} серий`
}

function episodeTitle(episode: IMovieEpisode): string {
  const name = episode.name?.trim()

  if (name) {
    return `${episode.number}. ${name}`
  }

  return `Серия ${episode.number}`
}

function toggleEpisode(episode: IMovieEpisode): void {
  if (!episode.description) {
    return
  }

  openedEpisodeId.value = openedEpisodeId.value === episode.id ? null : episode.id
}
</script>

<style lang="scss" scoped>
.movie-seasons {
  margin-top: 2rem;

  &__title {
    margin-bottom: 12px;
    font-size: 18px;
    font-weight: 600;
    color: #282f53;
  }

  &__list {
    background: #fff;
    border: 1px solid rgba(40, 47, 83, 0.08);
    border-radius: 12px;
    overflow: hidden;
  }
}

.movie-season {
  :deep(.movie-season__header) {
    padding: 10px 12px;
  }

  &__head {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-width: 0;
  }

  &__poster {
    flex: 0 0 48px;
    width: 48px;
    height: 72px;
    overflow: hidden;
    border-radius: 8px;
    background: rgba(40, 47, 83, 0.06);

    :deep(.q-img) {
      height: 100%;
    }
  }

  &__poster-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
  }

  &__info {
    flex: 1;
    min-width: 0;
  }

  &__name {
    font-size: 15px;
    font-weight: 600;
    color: #282f53;
  }

  &__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
    color: #777a8f;
    font-size: 13px;
  }

  &__watch,
  .movie-episode__watch {
    flex-shrink: 0;
    background: rgba(40, 47, 83, 0.06);
    color: #777a8f;
  }

  &__watch--active,
  .movie-episode__watch--active {
    background: $primary;
    color: #fff;
  }

  &__episodes {
    padding: 0 8px 8px 12px;
  }
}

.movie-episode {
  border-top: 1px solid rgba(40, 47, 83, 0.06);

  &__row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 4px;
  }

  &__row--expandable {
    cursor: pointer;
  }

  &__still {
    flex: 0 0 96px;
    width: 96px;
    height: 54px;
    overflow: hidden;
    border-radius: 8px;
    background: rgba(40, 47, 83, 0.06);

    :deep(.q-img) {
      height: 100%;
    }
  }

  &__still-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
  }

  &__info {
    flex: 1;
    min-width: 0;
  }

  &__name {
    font-size: 14px;
    font-weight: 600;
    color: #282f53;
  }

  &__date {
    margin-top: 2px;
    color: #777a8f;
    font-size: 13px;
  }

  &__chevron {
    color: #9aa0b8;
  }

  &__description {
    padding: 0 4px 12px 112px;
    color: #282f53;
    font-size: 14px;
    line-height: 1.5;
    white-space: pre-line;
  }
}

@media (max-width: 700px) {
  .movie-episode {
    &__still {
      flex-basis: 72px;
      width: 72px;
      height: 40px;
    }

    &__description {
      padding-left: 4px;
    }
  }
}
</style>
