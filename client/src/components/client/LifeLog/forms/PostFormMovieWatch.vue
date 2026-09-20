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
        label="Фильм"
        outlined
        dense
        clearable
        :rules="[() => canSubmit || 'Укажите название фильма']"
        hint="Начните вводить название — выберите из каталога или сохраните свой вариант"
        @filter="filterMovies"
        @input-value="onMovieInputValue"
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
              <q-item-label v-if="scope.opt.year" caption>{{ scope.opt.year }}</q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-select>
    </div>

    <div class="lifelog-post-form-actions flex justify-between q-pa-md">
      <div class="lifelog-post-form-actions__left">
        <div class="flex">
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
          <q-checkbox
            v-model="isNullTime"
            name="не учитывать время"
            label="не учитывать время"
          />
        </div>
      </div>
      <div class="lifelog-post-form-actions__right">
        <q-btn
          label="Отправить"
          color="primary"
          :loading="isSubmitting"
          :disable="isSubmitting || !canSubmit"
          :round="false"
          no-caps
          @click="submit"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMoviesResponse } from 'src/api/mappers/Movie/movie.mapper'
import { getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/postStore'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { IMovie } from 'src/types/Movie'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import AppDateField from 'src/components/default/AppDateField.vue'

const postStore = usePostStore()

const emit = defineEmits<{
  success: []
}>()

const datetime = ref(getCurrentDateTime())
const isNullTime = ref(false)
const isSubmitting = ref(false)

const selectedMovie = ref<IMovie | null>(null)
const movieInput = ref('')
const movieOptions = ref<IMovie[]>([])
const isSearchLoading = ref(false)

let searchRequestId = 0

const canSubmit = computed(() => {
  if (selectedMovie.value) {
    return true
  }

  return movieInput.value.trim().length > 0
})

const onMovieInputValue = (value: string) => {
  movieInput.value = value

  if (selectedMovie.value && value !== selectedMovie.value.title) {
    selectedMovie.value = null
  }
}

const filterMovies = (val: string, update: (fn: () => void) => void) => {
  const term = val.trim()
  movieInput.value = val

  if (term.length < 2) {
    update(() => {
      movieOptions.value = []
    })
    return
  }

  const requestId = ++searchRequestId
  isSearchLoading.value = true

  void movieApi
    .getMovies({ search: term })
    .then(response => {
      if (requestId !== searchRequestId) {
        return
      }

      update(() => {
        movieOptions.value = mapMoviesResponse(response)
      })
    })
    .catch(() => {
      if (requestId !== searchRequestId) {
        return
      }

      update(() => {
        movieOptions.value = []
      })
    })
    .finally(() => {
      if (requestId === searchRequestId) {
        isSearchLoading.value = false
      }
    })
}

const resetForm = () => {
  datetime.value = getCurrentDateTime()
  isNullTime.value = false
  selectedMovie.value = null
  movieInput.value = ''
  movieOptions.value = []
}

const submit = async () => {
  if (isSubmitting.value || !canSubmit.value) {
    return
  }

  isSubmitting.value = true

  const title = selectedMovie.value?.title ?? movieInput.value.trim()
  const postModel = {
    title,
    content: '',
    content_type: PostContentTypeEnum.MOVIE,
    tags: [],
    newTags: [],
    datetime: datetime.value,
    isNullTime: isNullTime.value,
    movie_id: selectedMovie.value ? Number(selectedMovie.value.id) : null,
    movie_title: selectedMovie.value ? null : movieInput.value.trim()
  }

  const submission = postStore.createPost(postModel, [])
  resetForm()
  emit('success')

  try {
    await submission
  } finally {
    isSubmitting.value = false
  }
}

watch(isNullTime, newValue => {
  const onlyDate = datetime.value.split(' ')[0]
  if (newValue) {
    datetime.value = onlyDate
  } else {
    datetime.value = `${onlyDate} 00:00`
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

  &-actions {
    background-color: #fbfbfb;
  }
}
</style>
