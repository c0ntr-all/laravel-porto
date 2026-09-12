<template>
  <div>
    <div class="row items-center q-mb-md">
      <div>
        <div class="text-h6">Genres</div>
        <div class="text-caption text-grey-7">
          Genres must exist before they can be attached to a title.
        </div>
      </div>
      <q-space />
      <q-btn
        icon="add"
        label="Create genre"
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

      <q-list v-if="store.genres.length" separator>
        <q-item v-for="genre in store.genres" :key="genre.id">
          <q-item-section>
            <q-item-label class="text-subtitle1 text-weight-medium">
              {{ genre.name }}
            </q-item-label>
            <q-item-label caption>
              {{ genre.slug }}
              <span v-if="genre.kp_id != null"> · KP {{ genre.kp_id }}</span>
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <div class="row no-wrap q-gutter-xs">
              <q-btn icon="edit" color="primary" flat round dense @click="openForm(genre)" />
              <q-btn icon="delete" color="negative" flat round dense @click="confirmDelete(genre)" />
            </div>
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="!store.isLoading" class="q-pa-lg text-grey-6">
        No genres yet.
      </div>
    </q-card>

    <MovieGenreFormDialog
      v-model="showForm"
      :genre="editing"
    />

    <q-dialog v-model="showDelete">
      <q-card style="min-width: 320px">
        <q-card-section class="text-h6">Delete genre</q-card-section>
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
import { onMounted, ref } from 'vue'
import { useMovieGenreStore } from 'src/stores/modules/movieGenreStore'
import { IMovieGenre } from 'src/types/Movie'
import MovieGenreFormDialog from 'src/components/admin/Movies/MovieGenreFormDialog.vue'

const store = useMovieGenreStore()
const showForm = ref(false)
const editing = ref<IMovieGenre | null>(null)
const showDelete = ref(false)
const deleting = ref<IMovieGenre | null>(null)

function openForm(genre?: IMovieGenre): void {
  editing.value = genre ?? null
  showForm.value = true
}

function confirmDelete(genre: IMovieGenre): void {
  deleting.value = genre
  showDelete.value = true
}

async function runDelete(): Promise<void> {
  if (!deleting.value) {
    return
  }

  const ok = await store.deleteGenre(deleting.value.id)

  if (ok) {
    showDelete.value = false
    deleting.value = null
  }
}

onMounted(() => {
  void store.getGenres()
})
</script>
