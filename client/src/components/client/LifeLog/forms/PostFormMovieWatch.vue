<template>
  <div class="lifelog-post-form">
    <div class="lifelog-post-form__movie q-pa-md">
      <q-select
        v-model="selectedMovie"
        :options="movieOptions"
        option-label="title"
        use-input
        fill-input
        hide-selected
        input-debounce="300"
        :loading="isSearchLoading"
        label="Фильм или сериал"
        outlined
        dense
        clearable
        :rules="[() => canSubmitMovie || 'Укажите название']"
        hint="Показаны последние добавленные — начните ввод для поиска или сохраните свой вариант"
        @filter="filterMovies"
        @input-value="onMovieInputValue"
        @popup-show="onMoviePopupShow"
      >
        <template #no-option>
          <q-item>
            <q-item-section class="text-grey">
              Ничего не найдено — можно сохранить введённое название
            </q-item-section>
          </q-item>
        </template>
        <template #option="scope">
          <q-item v-bind="scope.itemProps">
            <q-item-section>
              <q-item-label>{{ scope.opt.title }}</q-item-label>
              <q-item-label caption>
                {{ movieOptionCaption(scope.opt) }}
              </q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-select>

      <PostFormSeriesWatchFields
        v-if="isTvSeriesSelected && selectedMovie"
        v-model="watchProgress"
        :movie-id="selectedMovie.id"
        class="q-mt-md"
      />
    </div>

    <div class="lifelog-post-form__period q-px-md q-pb-sm">
      <div class="text-subtitle2 text-grey-8 q-mb-sm">Начало просмотра</div>
      <div class="flex items-center q-gutter-sm">
        <AppDatetimeField
          v-if="hasStartedAt && !isStartNullTime"
          class="lifelog-post-form__datetime"
          v-model="startDatetime"
        />
        <AppDateField
          v-else-if="hasStartedAt"
          class="lifelog-post-form__datetime"
          v-model="startDatetime"
        />
        <q-btn
          v-if="!hasStartedAt"
          flat
          dense
          no-caps
          color="primary"
          icon="add"
          label="Указать начало"
          @click="enableStartedAt"
        />
        <template v-else>
          <LifeLogNullTimeButton v-model="isStartNullTime" />
          <q-btn
            flat
            dense
            round
            color="grey-7"
            icon="close"
            @click="clearStartedAt"
          >
            <q-tooltip>Убрать дату начала</q-tooltip>
          </q-btn>
        </template>
      </div>
    </div>

    <div class="lifelog-post-form-actions flex wrap justify-between items-end q-pa-md">
      <div class="lifelog-post-form-actions__left">
        <div class="text-subtitle2 text-grey-8 q-mb-sm">Окончание</div>
        <div class="row items-center no-wrap q-gutter-xs">
          <AppDatetimeField
            v-if="!isNullTime"
            class="lifelog-post-form__datetime"
            v-model="datetime"
          />
          <AppDateField
            v-else
            class="lifelog-post-form__datetime"
            v-model="datetime"
          />
          <LifeLogNullTimeButton
            v-model="isNullTime"
            @update:model-value="onNullTimeChange"
          />
        </div>
      </div>
      <div class="lifelog-post-form-actions__right row items-center no-wrap q-gutter-sm self-end">
        <q-btn
          flat
          no-caps
          color="grey-8"
          label="Сохранить черновик"
          :loading="submitMode === 'draft'"
          :disable="isSubmitting || !canSubmit"
          @click="submit(true)"
        />
        <q-btn
          unelevated
          no-caps
          color="primary"
          label="Отправить"
          :loading="submitMode === 'publish'"
          :disable="isSubmitting || !canSubmit"
          @click="submit(false)"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMovieResponse, mapMoviesResponse } from 'src/api/mappers/Movie/movie.mapper'
import { applyIsNullTimeToDatetime, getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/postStore'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { MovieTypeEnum, MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { IMovie } from 'src/types/Movie'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'
import {
  emptySeriesWatchProgress,
  isSeriesWatchValid
} from 'src/utils/LifeLog/seriesWatch'
import { serializeStartedAt } from 'src/utils/LifeLog/startedAt'
import { markEpisodesWatchedIfNeeded } from 'src/utils/Movie/markEpisodesWatched'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import AppDateField from 'src/components/default/AppDateField.vue'
import PostFormSeriesWatchFields from 'src/components/client/LifeLog/forms/PostFormSeriesWatchFields.vue'
import LifeLogNullTimeButton from 'src/components/client/LifeLog/forms/LifeLogNullTimeButton.vue'

const postStore = usePostStore()

const emit = defineEmits<{
  success: []
}>()

const datetime = ref(getCurrentDateTime())
const isNullTime = ref(false)
const hasStartedAt = ref(false)
const startDatetime = ref('')
const isStartNullTime = ref(false)
const isSubmitting = ref(false)
const submitMode = ref<'draft' | 'publish' | null>(null)

const selectedMovie = ref<IMovie | null>(null)
const movieInput = ref('')
const movieOptions = ref<IMovie[]>([])
const isSearchLoading = ref(false)
const watchProgress = ref<ISeriesWatchProgress>(emptySeriesWatchProgress())

const RECENT_MOVIES_LIMIT = 5

let searchRequestId = 0

const isTvSeriesSelected = computed(() => {
  return selectedMovie.value?.type === MovieTypeEnum.TV_SERIES
})

const canSubmitMovie = computed(() => {
  if (selectedMovie.value) {
    return true
  }

  return movieInput.value.trim().length > 0
})

const canSubmit = computed(() => {
  if (!canSubmitMovie.value) {
    return false
  }

  if (isTvSeriesSelected.value) {
    return isSeriesWatchValid(watchProgress.value, { requireEpisodeSelection: true })
  }

  return true
})

const movieOptionCaption = (movie: IMovie) => {
  const typeLabel = MOVIE_TYPE_LABELS[movie.type] ?? movie.type
  return movie.year ? `${typeLabel} · ${movie.year}` : typeLabel
}

const onMovieInputValue = (value: string) => {
  movieInput.value = value

  if (selectedMovie.value && value !== selectedMovie.value.title) {
    selectedMovie.value = null
  }
}

const loadMovieOptions = (
  term: string,
  update?: (fn: () => void) => void
) => {
  const requestId = ++searchRequestId
  isSearchLoading.value = true

  const query = term.length > 0
    ? { search: term }
    : { sort: '-created_at' }

  void movieApi
    .getMovies(query)
    .then(response => {
      if (requestId !== searchRequestId) {
        return
      }

      const movies = mapMoviesResponse(response)
      const nextOptions = term.length > 0
        ? movies
        : movies.slice(0, RECENT_MOVIES_LIMIT)

      const apply = () => {
        movieOptions.value = nextOptions
      }

      if (update) {
        update(apply)
      } else {
        apply()
      }
    })
    .catch(() => {
      if (requestId !== searchRequestId) {
        return
      }

      const apply = () => {
        movieOptions.value = []
      }

      if (update) {
        update(apply)
      } else {
        apply()
      }
    })
    .finally(() => {
      if (requestId === searchRequestId) {
        isSearchLoading.value = false
      }
    })
}

const filterMovies = (val: string, update: (fn: () => void) => void) => {
  movieInput.value = val
  loadMovieOptions(val.trim(), update)
}

const onMoviePopupShow = () => {
  if (movieInput.value.trim().length > 0) {
    return
  }

  loadMovieOptions('')
}

const enableStartedAt = () => {
  hasStartedAt.value = true
  isStartNullTime.value = false
  startDatetime.value = applyIsNullTimeToDatetime(datetime.value.split(' ')[0], false)
}

const clearStartedAt = () => {
  hasStartedAt.value = false
  startDatetime.value = ''
  isStartNullTime.value = false
}

const resetForm = () => {
  datetime.value = getCurrentDateTime()
  isNullTime.value = false
  clearStartedAt()
  selectedMovie.value = null
  movieInput.value = ''
  movieOptions.value = []
  watchProgress.value = emptySeriesWatchProgress()
}

const submit = async (asDraft: boolean) => {
  if (isSubmitting.value || !canSubmit.value) {
    return
  }

  isSubmitting.value = true
  submitMode.value = asDraft ? 'draft' : 'publish'

  const isSeries = isTvSeriesSelected.value
  const movieId = selectedMovie.value?.id ?? null
  const episodeIdsToMark = isSeries
    ? [...(watchProgress.value.episode_ids ?? [])]
    : []
  const title = selectedMovie.value?.title ?? movieInput.value.trim()
  const startedAt = hasStartedAt.value
    ? serializeStartedAt(startDatetime.value, isStartNullTime.value)
    : null

  const postModel = {
    title,
    content: '',
    content_type: PostContentTypeEnum.MOVIE,
    is_draft: asDraft,
    tags: [],
    newTags: [],
    datetime: datetime.value,
    isNullTime: isNullTime.value,
    movie_id: selectedMovie.value ? Number(selectedMovie.value.id) : null,
    movie_title: selectedMovie.value ? null : movieInput.value.trim(),
    watch: isSeries ? { ...watchProgress.value } : null,
    started_at: startedAt
  }

  const submission = postStore.createPost(postModel, [])
  resetForm()
  emit('success')

  try {
    await submission

    if (isSeries && movieId && episodeIdsToMark.length) {
      try {
        const movieResponse = await movieApi.getMovie(movieId)
        const movie = mapMovieResponse(movieResponse)
        const episodes = (movie.seasons ?? []).flatMap(season => season.episodes)
        await markEpisodesWatchedIfNeeded(episodes, episodeIdsToMark)
      } catch {
        // Пост уже создан; отметки эпизодов можно повторить на странице сериала
      }
    }
  } finally {
    isSubmitting.value = false
    submitMode.value = null
  }
}

const onNullTimeChange = (isNullTime: boolean | null) => {
  datetime.value = applyIsNullTimeToDatetime(datetime.value, Boolean(isNullTime))
}

watch(isStartNullTime, newValue => {
  if (!hasStartedAt.value || !startDatetime.value) {
    return
  }

  startDatetime.value = applyIsNullTimeToDatetime(startDatetime.value, newValue)
})

watch(selectedMovie, movie => {
  if (movie?.type !== MovieTypeEnum.TV_SERIES) {
    watchProgress.value = emptySeriesWatchProgress()
  }
})

onMounted(() => {
  resetForm()
})
</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &__datetime {
    width: 240px;
  }

  &__period {
    background-color: #ffffff;
  }

  &-actions {
    gap: 12px;
    background-color: #fbfbfb;

    &__right {
      margin-left: auto;
    }
  }
}
</style>
