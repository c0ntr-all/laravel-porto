<template>
  <div>
    <div class="row items-end q-col-gutter-sm q-mb-md">
      <div class="col-12 col-md">
        <div class="text-h6">Catalog</div>
        <div class="text-caption text-grey-7">
          Add movies, TV series and shows with Kinopoisk fields.
        </div>
      </div>
      <div class="col-12 col-sm-4 col-md-3">
        <q-input
          v-model="searchText"
          label="Title"
          outlined
          dense
          debounce="400"
          clearable
          @update:model-value="onSearch"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-auto">
        <q-btn
          icon="add"
          label="Add title"
          color="primary"
          unelevated
          no-caps
          @click="openForm()"
        />
      </div>
    </div>

    <q-btn-toggle
      v-model="typeFilter"
      class="q-mb-md"
      unelevated
      dense
      no-caps
      toggle-color="primary"
      color="grey-2"
      text-color="primary"
      :options="typeOptions"
      @update:model-value="onTypeChange"
    />

    <q-card flat bordered>
      <q-inner-loading :showing="movieStore.isMoviesLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="movieStore.movies.length" separator>
        <q-item
          v-for="item in movieStore.movies"
          :key="item.id"
          class="movie-row"
        >
          <q-item-section avatar>
            <q-avatar size="56px" rounded>
              <img v-if="poster(item)" :src="poster(item) ?? ''" :alt="item.title">
              <q-icon v-else name="movie" />
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-subtitle1 text-weight-medium">
              {{ item.title }}
            </q-item-label>
            <q-item-label caption>
              {{ MOVIE_TYPE_LABELS[item.type] }}
              <span v-if="item.year"> · {{ item.year }}</span>
              <span v-if="item.kp_rating != null"> · {{ item.kp_rating.toFixed(1) }}</span>
            </q-item-label>
            <div v-if="item.genres.length" class="q-gutter-xs q-mt-xs">
              <q-chip
                v-for="genre in item.genres.slice(0, 4)"
                :key="genre.id"
                size="sm"
                outline
                dense
              >
                {{ genre.name }}
              </q-chip>
            </div>
          </q-item-section>
          <q-item-section side>
            <div class="row no-wrap q-gutter-xs">
              <q-btn icon="edit" color="primary" flat round dense @click="openForm(item)">
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              <q-btn icon="delete" color="negative" flat round dense @click="confirmDelete(item)">
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
            </div>
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="!movieStore.isMoviesLoading" class="q-pa-lg text-grey-6">
        No titles yet. Add a movie, series or show to start the catalog.
      </div>

      <div v-if="movieStore.hasMoreMovies" ref="sentinel" class="list-sentinel" />
      <div v-if="movieStore.isMoviesLoadingMore" class="flex justify-center q-py-md">
        <q-spinner color="primary" />
      </div>
    </q-card>

    <MovieFormDialog
      v-model="showForm"
      :movie="editing"
    />

    <q-dialog v-model="showDelete">
      <q-card style="min-width: 320px">
        <q-card-section class="text-h6">Delete title</q-card-section>
        <q-card-section>
          Delete “{{ deleting?.title }}”?
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat v-close-popup>Cancel</q-btn>
          <q-btn
            color="negative"
            unelevated
            :loading="movieStore.isSaving"
            @click="runDelete"
          >
            Delete
          </q-btn>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMovieAdminStore } from 'src/stores/modules/movieAdminStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { MovieTypeEnum, MOVIE_TYPE_LABELS } from 'src/enums/Movie/MovieTypeEnum'
import { moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'
import { IMovie } from 'src/types/Movie'
import MovieFormDialog from 'src/components/admin/Movies/MovieFormDialog.vue'

const movieStore = useMovieAdminStore()
const searchText = ref(movieStore.listTitle)
const typeFilter = ref<MovieTypeEnum | 'all'>(movieStore.listType ?? 'all')
const showForm = ref(false)
const editing = ref<IMovie | null>(null)
const showDelete = ref(false)
const deleting = ref<IMovie | null>(null)

const typeOptions = [
  { label: 'All', value: 'all' },
  { label: MOVIE_TYPE_LABELS[MovieTypeEnum.MOVIE], value: MovieTypeEnum.MOVIE },
  { label: MOVIE_TYPE_LABELS[MovieTypeEnum.TV_SERIES], value: MovieTypeEnum.TV_SERIES },
  { label: MOVIE_TYPE_LABELS[MovieTypeEnum.SHOW], value: MovieTypeEnum.SHOW }
]

function resolvedType(): MovieTypeEnum | null {
  return typeFilter.value === 'all' ? null : typeFilter.value
}

function poster(item: IMovie): string | null {
  return moviePosterUrl(item)
}

function onSearch(value: string | number | null): void {
  void movieStore.getMovies({
    title: value == null ? '' : String(value),
    type: resolvedType()
  })
}

function onTypeChange(): void {
  void movieStore.getMovies({
    title: searchText.value,
    type: resolvedType()
  })
}

function openForm(item?: IMovie): void {
  editing.value = item ?? null
  showForm.value = true
}

function confirmDelete(item: IMovie): void {
  deleting.value = item
  showDelete.value = true
}

async function runDelete(): Promise<void> {
  if (!deleting.value) {
    return
  }

  const ok = await movieStore.deleteMovie(deleting.value.id)

  if (ok) {
    showDelete.value = false
    deleting.value = null
  }
}

const { sentinel } = useScrollSentinel(
  () => { void movieStore.getMovies({ append: true }) },
  () => movieStore.hasMoreMovies && !movieStore.isMoviesLoading && !movieStore.isMoviesLoadingMore
)

onMounted(() => {
  void movieStore.getMovies({
    title: searchText.value,
    type: resolvedType()
  })
})
</script>

<style lang="scss" scoped>
.movie-row {
  min-height: 84px;
}

.list-sentinel {
  height: 1px;
}
</style>
