<template>
  <div class="post-form-series-watch q-gutter-sm">
    <div class="text-subtitle2 text-grey-8">Прогресс просмотра</div>

    <q-inner-loading :showing="isSeasonsLoading" color="primary" />

    <template v-if="!isSeasonsLoading && seasons.length === 0 && movieId">
      <div class="text-caption text-grey-7">
        У сериала пока нет сезонов в каталоге — добавьте их на странице фильма.
      </div>
    </template>

    <template v-else-if="seasons.length">
      <q-select
        v-model="selectedSeason"
        :options="seasons"
        :option-label="seasonOptionLabel"
        label="Сезон"
        outlined
        dense
        :rules="[() => !!selectedSeason || 'Выберите сезон']"
        @update:model-value="onSeasonChange"
      />

      <q-select
        v-model="selectedEpisodes"
        :options="episodeOptions"
        :option-label="episodeOptionLabel"
        label="Эпизоды"
        outlined
        dense
        multiple
        use-chips
        stack-label
        :disable="!selectedSeason"
        :rules="[() => selectedEpisodes.length > 0 || 'Выберите хотя бы один эпизод']"
        @update:model-value="emitFromSelection"
      >
        <template #option="scope">
          <q-item v-bind="scope.itemProps">
            <q-item-section>
              <q-item-label>{{ episodeOptionLabel(scope.opt) }}</q-item-label>
              <q-item-label v-if="scope.opt.is_watched" caption class="text-positive">
                Уже отмечен просмотренным
              </q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-select>
    </template>

    <q-input
      v-model="stoppedAtInput"
      label="Остановился на (если не до конца)"
      hint="Время на последней серии, например 00:32 или 00:32:15"
      outlined
      dense
      clearable
      placeholder="00:32:15"
      :rules="[
        val => !val || /^\d{1,2}:\d{2}(:\d{2})?$/.test(String(val).trim()) || 'Формат H:i или H:i:s'
      ]"
      @update:model-value="onStoppedAtChange"
    />
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMovieResponse } from 'src/api/mappers/Movie/movie.mapper'
import { IMovieEpisode, IMovieSeason } from 'src/types/Movie'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'
import { emptySeriesWatchProgress } from 'src/utils/LifeLog/seriesWatch'

const props = defineProps<{
  movieId: string | null
  modelValue: ISeriesWatchProgress | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: ISeriesWatchProgress]
}>()

const seasons = ref<IMovieSeason[]>([])
const isSeasonsLoading = ref(false)
const selectedSeason = ref<IMovieSeason | null>(null)
const selectedEpisodes = ref<IMovieEpisode[]>([])
const stoppedAtInput = ref(props.modelValue?.stopped_at ?? '')

let loadRequestId = 0

const episodeOptions = computed(() => selectedSeason.value?.episodes ?? [])

function seasonOptionLabel (season: IMovieSeason): string {
  if (season.name?.trim()) {
    return `Сезон ${season.number}: ${season.name.trim()}`
  }

  return `Сезон ${season.number}`
}

function episodeOptionLabel (episode: IMovieEpisode): string {
  const title = episode.name?.trim()
  return title ? `${episode.number}. ${title}` : `Эпизод ${episode.number}`
}

function buildWatchFromSelection (): ISeriesWatchProgress {
  const season = selectedSeason.value
  const episodes = [...selectedEpisodes.value].sort((a, b) => a.number - b.number)

  if (!season || !episodes.length) {
    return {
      ...emptySeriesWatchProgress(),
      stopped_at: stoppedAtInput.value.trim() || null,
      episode_ids: []
    }
  }

  const numbers = episodes.map(episode => episode.number)

  return {
    season: season.number,
    episode_from: numbers[0],
    episode_to: numbers[numbers.length - 1],
    stopped_at: stoppedAtInput.value.trim() || null,
    episode_ids: episodes.map(episode => episode.id)
  }
}

function emitFromSelection () {
  emit('update:modelValue', buildWatchFromSelection())
}

function onSeasonChange () {
  selectedEpisodes.value = []
  emitFromSelection()
}

function onStoppedAtChange (value: string | number | null) {
  const raw = String(value ?? '').trim()
  stoppedAtInput.value = raw === '00:00:00' ? '' : raw
  emitFromSelection()
}

function applyModelToSelection (value: ISeriesWatchProgress | null) {
  const next = value ?? emptySeriesWatchProgress()
  stoppedAtInput.value = next.stopped_at ?? ''

  if (!seasons.value.length) {
    return
  }

  const season =
    seasons.value.find(item => item.number === next.season) ??
    seasons.value[0] ??
    null

  selectedSeason.value = season

  if (!season) {
    selectedEpisodes.value = []
    return
  }

  if (next.episode_ids?.length) {
    const idSet = new Set(next.episode_ids)
    selectedEpisodes.value = season.episodes.filter(episode => idSet.has(episode.id))
    return
  }

  selectedEpisodes.value = season.episodes.filter(
    episode =>
      episode.number >= next.episode_from &&
      episode.number <= next.episode_to
  )
}

async function loadSeasons (movieId: string) {
  const requestId = ++loadRequestId
  isSeasonsLoading.value = true
  seasons.value = []
  selectedSeason.value = null
  selectedEpisodes.value = []

  try {
    const response = await movieApi.getMovie(movieId)
    if (requestId !== loadRequestId) {
      return
    }

    const movie = mapMovieResponse(response)
    seasons.value = movie.seasons ?? []
    applyModelToSelection(props.modelValue)
    emitFromSelection()
  } catch {
    if (requestId !== loadRequestId) {
      return
    }
    seasons.value = []
  } finally {
    if (requestId === loadRequestId) {
      isSeasonsLoading.value = false
    }
  }
}

watch(
  () => props.movieId,
  movieId => {
    if (!movieId) {
      loadRequestId += 1
      seasons.value = []
      selectedSeason.value = null
      selectedEpisodes.value = []
      isSeasonsLoading.value = false
      return
    }

    void loadSeasons(movieId)
  },
  { immediate: true }
)

watch(
  () => props.modelValue,
  value => {
    if (!seasons.value.length) {
      stoppedAtInput.value = value?.stopped_at ?? ''
      return
    }

    applyModelToSelection(value)
  },
  { deep: true }
)
</script>

<style lang="scss" scoped>
.post-form-series-watch {
  position: relative;
  width: 100%;
  min-height: 2rem;
}
</style>
