<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card class="movie-form-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ title }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-select
          v-model="model.type"
          :options="typeOptions"
          label="Type"
          emit-value
          map-options
          filled
          dense
        />
        <q-input
          v-model="model.title"
          label="Title"
          filled
          dense
          :rules="[value => Boolean(String(value).trim()) || 'Required']"
        />
        <q-input
          v-model="model.description"
          label="Description"
          type="textarea"
          filled
          dense
          autogrow
          hint="Optional, up to 10000 characters"
        />
        <div class="row q-col-gutter-md">
          <div class="col-12 col-sm-6">
            <q-input
              v-model.number="model.year"
              label="Year"
              type="number"
              filled
              dense
              :rules="[validateYear]"
            />
          </div>
          <div class="col-12 col-sm-6">
            <q-input
              v-model.number="model.kp_id"
              label="Kinopoisk ID"
              type="number"
              filled
              dense
              :rules="[validateKpId]"
            />
          </div>
        </div>
        <q-input
          v-model.number="model.kp_rating"
          label="Kinopoisk rating"
          type="number"
          step="0.1"
          filled
          dense
          hint="Optional, 0–10"
          :rules="[validateRating]"
        />
        <q-input
          v-model="model.cover"
          label="Cover URL"
          filled
          dense
          hint="Optional poster URL"
          :rules="[validateUrl]"
        />
        <q-input
          v-model="model.kp_img"
          label="Kinopoisk image URL"
          filled
          dense
          hint="Optional fallback poster URL"
          :rules="[validateUrl]"
        />
        <q-select
          v-model="model.genre_ids"
          :options="genreOptions"
          label="Genres"
          emit-value
          map-options
          multiple
          use-chips
          clearable
          filled
          dense
          hint="Optional. Create genres in the Genres tab first."
        />
      </q-card-section>

      <q-card-section align="right">
        <q-btn
          :label="movie ? 'Save' : 'Create'"
          color="primary"
          :loading="movieStore.isSaving"
          :disable="!canSubmit"
          @click="submit"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, onMounted, reactive, watch } from 'vue'
import { useMovieAdminStore } from 'src/stores/modules/movieAdminStore'
import { useMovieGenreStore } from 'src/stores/modules/movieGenreStore'
import { MovieTypeEnum, MOVIE_TYPE_LABELS, MOVIE_TYPES } from 'src/enums/Movie/MovieTypeEnum'
import { IMovie, IMovieWriteDto } from 'src/types/Movie'

const props = defineProps<{
  modelValue: boolean
  movie?: IMovie | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const movieStore = useMovieAdminStore()
const genreStore = useMovieGenreStore()

const model = reactive({
  type: MovieTypeEnum.MOVIE as MovieTypeEnum,
  title: '',
  description: '',
  year: new Date().getFullYear(),
  kp_id: null as number | null,
  kp_rating: null as number | null,
  cover: '',
  kp_img: '',
  genre_ids: [] as string[]
})

const typeOptions = MOVIE_TYPES.map(type => ({
  label: MOVIE_TYPE_LABELS[type],
  value: type
}))

const genreOptions = computed(() => genreStore.genres.map(genre => ({
  label: genre.name,
  value: genre.id
})))

const title = computed(() => (
  props.movie ? `Edit ${MOVIE_TYPE_LABELS[props.movie.type]}` : 'Add title'
))

function validateYear(value: number | string | null): true | string {
  const year = Number(value)

  if (!Number.isInteger(year) || year < 1888 || year > 2100) {
    return 'Year must be between 1888 and 2100'
  }

  return true
}

function validateKpId(value: number | string | null): true | string {
  const kpId = Number(value)

  if (!Number.isInteger(kpId) || kpId < 1) {
    return 'Kinopoisk ID must be a positive integer'
  }

  return true
}

function validateRating(value: number | string | null): true | string {
  if (value === null || value === '' || Number.isNaN(Number(value))) {
    return true
  }

  const rating = Number(value)

  if (rating < 0 || rating > 10) {
    return 'Rating must be between 0 and 10'
  }

  return true
}

function validateUrl(value: string | null): true | string {
  const trimmed = String(value ?? '').trim()

  if (!trimmed) {
    return true
  }

  try {
    const url = new URL(trimmed)

    if (url.protocol !== 'http:' && url.protocol !== 'https:') {
      return 'Must be an http(s) URL'
    }

    return true
  } catch {
    return 'Must be a valid URL'
  }
}

const canSubmit = computed(() => (
  Boolean(model.title.trim()) &&
  validateYear(model.year) === true &&
  validateKpId(model.kp_id) === true &&
  validateRating(model.kp_rating) === true &&
  validateUrl(model.cover) === true &&
  validateUrl(model.kp_img) === true
))

function emptyRating(value: number | null): boolean {
  return value === null || Number.isNaN(Number(value))
}

function payload(): IMovieWriteDto {
  return {
    kp_id: Number(model.kp_id),
    title: model.title.trim(),
    description: model.description.trim() || null,
    year: Number(model.year),
    type: model.type,
    cover: model.cover.trim() || null,
    kp_rating: emptyRating(model.kp_rating) ? null : Number(model.kp_rating),
    kp_img: model.kp_img.trim() || null,
    genre_ids: model.genre_ids.map(id => Number(id))
  }
}

async function submit(): Promise<void> {
  const saved = props.movie
    ? await movieStore.updateMovie(props.movie.id, payload())
    : await movieStore.createMovie(payload())

  if (saved) {
    emit('update:modelValue', false)
  }
}

watch(() => [props.modelValue, props.movie] as const, ([open]) => {
  if (!open) {
    return
  }

  model.type = props.movie?.type ?? MovieTypeEnum.MOVIE
  model.title = props.movie?.title ?? ''
  model.description = props.movie?.description ?? ''
  model.year = props.movie?.year ?? new Date().getFullYear()
  model.kp_id = props.movie?.kp_id ?? null
  model.kp_rating = props.movie?.kp_rating ?? null
  model.cover = props.movie?.cover ?? ''
  model.kp_img = props.movie?.kp_img ?? ''
  model.genre_ids = props.movie?.genres.map(genre => genre.id) ?? []
})

onMounted(() => {
  if (!genreStore.genres.length) {
    void genreStore.getGenres()
  }
})
</script>

<style lang="scss" scoped>
.movie-form-dialog {
  min-width: 480px;
  width: 100%;
  max-width: 640px;
}
</style>
