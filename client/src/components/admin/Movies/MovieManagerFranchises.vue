<template>
  <div>
    <div class="row items-center q-mb-md">
      <div>
        <div class="text-h6">Franchises</div>
        <div class="text-caption text-grey-7">
          Group films that share the same universe and control their display order.
        </div>
      </div>
      <q-space />
      <q-btn
        icon="add"
        label="Create franchise"
        color="primary"
        unelevated
        no-caps
        @click="openForm()"
      />
    </div>

    <q-card flat bordered>
      <q-inner-loading :showing="store.isLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="store.franchises.length" separator>
        <q-expansion-item
          v-for="item in store.franchises"
          :key="item.id"
          expand-separator
          @show="onExpand(item.id)"
        >
          <template #header>
            <div class="franchise-row">
              <div class="franchise-row__poster">
                <q-img
                  v-if="franchiseImageUrl(item)"
                  :src="franchiseImageUrl(item) ?? ''"
                  :alt="item.name"
                  fit="cover"
                >
                  <template #error>
                    <div class="franchise-row__poster-fallback">
                      <q-icon name="collections_bookmark" />
                    </div>
                  </template>
                </q-img>
                <div v-else class="franchise-row__poster-fallback">
                  <q-icon name="collections_bookmark" />
                </div>
              </div>
              <div class="franchise-row__name">{{ item.name }}</div>
              <div class="franchise-row__actions" @click.stop>
                <q-btn icon="edit" color="primary" flat round dense @click="openForm(item)" />
                <q-btn icon="delete" color="negative" flat round dense @click="confirmDelete(item)" />
              </div>
            </div>
          </template>

          <div class="franchise-row__body">
            <div
              v-if="item.description"
              class="franchise-row__description"
              v-html="item.description"
            />
            <p v-else class="franchise-row__description text-grey-6">
              No description yet.
            </p>

            <MovieManagerFranchiseMovies
              :franchise-id="item.id"
              :movies="moviesFor(item.id)"
            />
          </div>
        </q-expansion-item>
      </q-list>

      <div v-else-if="!store.isLoading" class="q-pa-lg text-grey-6">
        No franchises yet.
      </div>
    </q-card>

    <MovieFranchiseFormDialog
      v-model="showForm"
      :franchise="editing"
      @saved="onSaved"
    />

    <q-dialog v-model="showDelete">
      <q-card style="min-width: 320px">
        <q-card-section class="text-h6">Delete franchise</q-card-section>
        <q-card-section>
          Delete “{{ deleting?.name }}”?
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat v-close-popup>Cancel</q-btn>
          <q-btn
            color="negative"
            unelevated
            :loading="store.isSaving"
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
import { onMounted, ref, watch } from 'vue'
import { useMovieFranchiseStore } from 'src/stores/modules/movieFranchiseStore'
import { franchiseImageUrl } from 'src/api/mappers/Movie/movie.mapper'
import { IMovieFranchise, IMovieFranchiseMovie } from 'src/types/Movie'
import MovieFranchiseFormDialog from 'src/components/admin/Movies/MovieFranchiseFormDialog.vue'
import MovieManagerFranchiseMovies from 'src/components/admin/Movies/MovieManagerFranchiseMovies.vue'

const store = useMovieFranchiseStore()
const showForm = ref(false)
const editing = ref<IMovieFranchise | null>(null)
const showDelete = ref(false)
const deleting = ref<IMovieFranchise | null>(null)
const moviesByFranchise = ref<Record<string, IMovieFranchiseMovie[]>>({})

function moviesFor(franchiseId: string): IMovieFranchiseMovie[] {
  return moviesByFranchise.value[franchiseId] ?? []
}

function openForm(franchise?: IMovieFranchise): void {
  editing.value = franchise ?? null
  showForm.value = true
}

function confirmDelete(franchise: IMovieFranchise): void {
  deleting.value = franchise
  showDelete.value = true
}

async function runDelete(): Promise<void> {
  if (!deleting.value) {
    return
  }

  const ok = await store.deleteFranchise(deleting.value.id)

  if (ok) {
    delete moviesByFranchise.value[deleting.value.id]
    showDelete.value = false
    deleting.value = null
  }
}

async function onExpand(franchiseId: string): Promise<void> {
  const movies = await store.getFranchiseMovies(franchiseId)
  moviesByFranchise.value = {
    ...moviesByFranchise.value,
    [franchiseId]: movies
  }
}

async function onSaved(): Promise<void> {
  await store.getFranchises()

  if (editing.value) {
    await onExpand(editing.value.id)
  }
}

watch(
  () => [store.franchiseMoviesId, store.franchiseMovies] as const,
  ([franchiseId, movies]) => {
    if (!franchiseId) {
      return
    }

    moviesByFranchise.value = {
      ...moviesByFranchise.value,
      [franchiseId]: [...movies]
    }
  },
  { deep: true }
)

onMounted(() => {
  void store.getFranchises()
})
</script>

<style lang="scss" scoped>
.franchise-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  min-width: 0;

  &__poster {
    flex: 0 0 44px;
    width: 44px;
    height: 66px;
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

  &__name {
    flex: 1;
    min-width: 0;
    font-size: 15px;
    font-weight: 600;
    color: #282f53;
  }

  &__actions {
    display: flex;
    gap: 4px;
  }

  &__body {
    padding: 0 16px 16px 68px;
  }

  &__description {
    margin: 0 0 12px;
    color: #55586d;
    font-size: 14px;
    line-height: 1.5;

    :deep(p) {
      margin: 0 0 0.5em;
    }

    :deep(p:last-child) {
      margin-bottom: 0;
    }

    :deep(ul),
    :deep(ol) {
      margin: 0.25em 0 0.5em;
      padding-left: 1.25em;
    }
  }
}

@media (max-width: 700px) {
  .franchise-row__body {
    padding-left: 16px;
  }
}
</style>
