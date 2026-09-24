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
        :rules="[() => !!selectedMovie || !!movieInput.trim() || 'Укажите название']"
        @filter="filterMovies"
        @input-value="onMovieInputValue"
        @popup-show="onMoviePopupShow"
      >
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
        v-model="watchModel"
        :movie-id="selectedMovie.id"
        class="q-mt-md"
      />
    </div>

    <div class="lifelog-post-form__title q-px-md q-pt-md q-pb-sm">
      <q-input
        v-model="model.title"
        class="q-pa-none"
        label="Заголовок"
        dense
        outlined
      />
    </div>

    <div class="lifelog-post-form__content q-px-md q-pt-md q-pb-sm">
      <q-editor
        v-model="model.content"
        min-height="5rem"
        style="border-radius: 0"
      />
    </div>

    <div class="lifelog-post-form__tags q-pa-md">
      <PostFormCreateTags
        ref="formTagsRef"
        v-model="model"
      />
    </div>

    <div class="lifelog-post-form-actions flex justify-between q-pa-md">
      <div class="lifelog-post-form-actions__left">
        <div class="flex">
          <AppDatetimeField
            v-if="!model.isNullTime"
            class="lifelog-post-form__datetime"
            v-model="model.datetime"
          />
          <AppDateField
            v-else
            class="lifelog-post-form__datetime"
            v-model="model.datetime"
          />
          <q-checkbox
            v-model="model.isNullTime"
            name="не учитывать время"
            label="не учитывать время"
          />
        </div>
      </div>
      <div class="lifelog-post-form-actions__right">
        <q-btn
          label="Сохранить"
          color="primary"
          no-caps
          :loading="isSubmitting"
          :disable="isSubmitting || !canSubmit"
          :round="false"
          @click="submit"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, toRaw, watch } from 'vue'
import { getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/postStore'
import { IPost, IPostUpdateModel } from 'src/types'
import { IMovie } from 'src/types/Movie'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { MovieTypeEnum, MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMovieResponse, mapMoviesResponse } from 'src/api/mappers/Movie/movie.mapper'
import {
  emptySeriesWatchProgress,
  isSeriesWatchValid,
  normalizeSeriesWatchProgress
} from 'src/utils/LifeLog/seriesWatch'
import { markEpisodesWatchedIfNeeded } from 'src/utils/Movie/markEpisodesWatched'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import AppDateField from 'src/components/default/AppDateField.vue'
import PostFormCreateTags from 'src/components/client/LifeLog/forms/PostFormCreateTags.vue'
import PostFormSeriesWatchFields from 'src/components/client/LifeLog/forms/PostFormSeriesWatchFields.vue'

interface ITagsRef {
  resetAvailableTags: () => void
}

const props = defineProps<{
  post: IPost
}>()

const emit = defineEmits<{
  success: []
}>()

const postStore = usePostStore()

const model = ref<IPostUpdateModel>({
  title: '',
  content: '',
  content_type: PostContentTypeEnum.MOVIE,
  tags: [],
  newTags: [],
  datetime: getCurrentDateTime(),
  isNullTime: false,
  attachments: [],
  movie_id: null,
  movie_title: null,
  watch: emptySeriesWatchProgress()
})

const watchModel = computed({
  get (): ISeriesWatchProgress {
    return model.value.watch ?? emptySeriesWatchProgress()
  },
  set (value: ISeriesWatchProgress) {
    model.value.watch = value
  }
})

const originalPost = ref<IPostUpdateModel | null>(null)
const isSubmitting = ref(false)
const formTagsRef = ref<ITagsRef | null>(null)

const selectedMovie = ref<IMovie | null>(null)
const movieInput = ref('')
const movieOptions = ref<IMovie[]>([])
const isSearchLoading = ref(false)

const RECENT_MOVIES_LIMIT = 5
let searchRequestId = 0

const isTvSeriesSelected = computed(() => {
  return selectedMovie.value?.type === MovieTypeEnum.TV_SERIES ||
    model.value.content_type === PostContentTypeEnum.TV_SERIES
})

const canSubmit = computed(() => {
  if (!selectedMovie.value && !movieInput.value.trim()) {
    return false
  }

  if (isTvSeriesSelected.value) {
    return isSeriesWatchValid(model.value.watch, { requireEpisodeSelection: true })
  }

  return true
})

const movieOptionCaption = (movie: IMovie) => {
  const typeLabel = MOVIE_TYPE_LABELS[movie.type] ?? movie.type
  return movie.year ? `${typeLabel} · ${movie.year}` : typeLabel
}

function mapPostToModel (post: IPost): IPostUpdateModel {
  const rawPost = toRaw(post)
  const isSeries =
    rawPost.content_type === PostContentTypeEnum.TV_SERIES ||
    rawPost.movie?.type === MovieTypeEnum.TV_SERIES

  return {
    title: rawPost.title ?? '',
    content: rawPost.content ?? '',
    content_type: isSeries
      ? PostContentTypeEnum.TV_SERIES
      : (rawPost.content_type ?? PostContentTypeEnum.MOVIE),
    tags: [...(rawPost.tags ?? [])],
    newTags: [],
    datetime: rawPost.time ? `${rawPost.date} ${rawPost.time}` : rawPost.date,
    isNullTime: !rawPost.time,
    attachments: [],
    movie_id: rawPost.movie ? Number(rawPost.movie.id) : null,
    movie_title: null,
    watch: isSeries
      ? (normalizeSeriesWatchProgress(rawPost.watch) ?? emptySeriesWatchProgress())
      : null
  }
}

function syncMovieFieldsToModel () {
  if (selectedMovie.value) {
    model.value.movie_id = Number(selectedMovie.value.id)
    model.value.movie_title = null
    model.value.content_type = selectedMovie.value.type === MovieTypeEnum.TV_SERIES
      ? PostContentTypeEnum.TV_SERIES
      : PostContentTypeEnum.MOVIE

    if (selectedMovie.value.type === MovieTypeEnum.TV_SERIES) {
      model.value.watch = model.value.watch ?? emptySeriesWatchProgress()
    } else {
      model.value.watch = null
    }

    return
  }

  model.value.movie_id = null
  model.value.movie_title = movieInput.value.trim() || null
  model.value.content_type = PostContentTypeEnum.MOVIE
  model.value.watch = null
}

const onMovieInputValue = (value: string) => {
  movieInput.value = value

  if (selectedMovie.value && value !== selectedMovie.value.title) {
    selectedMovie.value = null
  }

  syncMovieFieldsToModel()
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

async function submit () {
  if (isSubmitting.value || !originalPost.value || !canSubmit.value) {
    return
  }

  syncMovieFieldsToModel()
  isSubmitting.value = true

  const movieId = selectedMovie.value?.id ?? null
  const episodeIdsToMark = isTvSeriesSelected.value
    ? [...(model.value.watch?.episode_ids ?? [])]
    : []

  try {
    const updatedPost = await postStore.updatePost(
      props.post.id,
      model.value,
      props.post,
      []
    )

    if (updatedPost) {
      const nextModel = mapPostToModel(updatedPost)
      model.value = nextModel
      originalPost.value = structuredClone(nextModel)
      selectedMovie.value = updatedPost.movie ?? null
      movieInput.value = updatedPost.movie?.title ?? ''
      formTagsRef.value?.resetAvailableTags()
      emit('success')

      if (movieId && episodeIdsToMark.length) {
        try {
          const movieResponse = await movieApi.getMovie(movieId)
          const movie = mapMovieResponse(movieResponse)
          const episodes = (movie.seasons ?? []).flatMap(season => season.episodes)
          await markEpisodesWatchedIfNeeded(episodes, episodeIdsToMark)
        } catch {
          // ignore
        }
      }
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(() => model.value.isNullTime, newValue => {
  const onlyDate = model.value.datetime.split(' ')[0]
  if (newValue) {
    model.value.datetime = onlyDate
  } else {
    model.value.datetime = `${onlyDate} 00:00`
  }
})

watch(selectedMovie, () => {
  syncMovieFieldsToModel()
})

onMounted(() => {
  const initial = mapPostToModel(props.post)
  model.value = initial
  originalPost.value = structuredClone(initial)
  selectedMovie.value = props.post.movie ?? null
  movieInput.value = props.post.movie?.title ?? ''
})
</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &__datetime {
    width: 240px;
  }

  &-actions {
    background-color: #fbfbfb;
  }
}
</style>
