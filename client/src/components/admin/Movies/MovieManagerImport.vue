<template>
  <div>
    <div class="q-mb-md">
      <div class="text-h6">Import from Kinopoisk</div>
      <div class="text-caption text-grey-7">
        Send a Kinopoisk film ID. Title, year, type, rating, poster, genres and countries will be filled automatically.
      </div>
    </div>

    <q-card class="q-mb-lg" flat bordered>
      <q-card-section>
        <form class="row items-end q-col-gutter-md" @submit.prevent="submit">
          <div class="col">
            <q-input
              v-model="kpInput"
              ref="kpInputRef"
              label="Kinopoisk ID or URL"
              hint="Example: 326 or https://www.kinopoisk.ru/film/326/"
              outlined
              dense
              lazy-rules
              :disable="store.isImporting"
              :rules="[validateInput]"
            >
              <template #prepend>
                <q-icon name="movie" />
              </template>
            </q-input>
          </div>
          <div class="col-auto">
            <q-btn
              type="submit"
              icon="cloud_download"
              label="Import"
              color="primary"
              unelevated
              no-caps
              :loading="store.isImporting"
              :disable="!canSubmit"
            />
          </div>
        </form>
      </q-card-section>
    </q-card>

    <div class="text-h6 q-mb-md">Import history</div>

    <q-card flat bordered>
      <q-inner-loading :showing="store.isLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="store.imports.length" separator>
        <q-item
          v-for="item in store.imports"
          :key="item.id"
          class="import-row"
        >
          <q-item-section avatar>
            <q-avatar size="56px" rounded>
              <img
                v-if="item.movie && poster(item.movie)"
                :src="poster(item.movie) ?? ''"
                :alt="item.movie.title"
              >
              <q-icon v-else name="cloud_download" />
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-subtitle1 text-weight-medium">
              {{ item.movie?.title || `KP ${item.kp_id}` }}
            </q-item-label>
            <q-item-label caption>
              KP ID {{ item.kp_id }}
              <span v-if="item.movie?.year"> · {{ item.movie.year }}</span>
              <span v-if="durationLabel(item)"> · {{ durationLabel(item) }}</span>
            </q-item-label>
            <q-item-label v-if="item.error_message" caption class="text-negative">
              {{ item.error_message }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <div class="column items-end q-gutter-xs">
              <q-chip
                dense
                :color="statusColor(item.status)"
                text-color="white"
              >
                {{ MOVIE_IMPORT_STATUS_LABELS[item.status] }}
              </q-chip>
              <q-chip
                v-if="item.status === MovieImportStatusEnum.COMPLETED && item.was_created != null"
                dense
                outline
                color="primary"
              >
                {{ item.was_created ? 'Created' : 'Updated' }}
              </q-chip>
              <q-btn
                v-if="item.source_url"
                dense
                flat
                no-caps
                size="sm"
                icon="open_in_new"
                label="Kinopoisk"
                :href="item.source_url"
                target="_blank"
                rel="noopener noreferrer"
              />
            </div>
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="!store.isLoading" class="q-pa-lg text-grey-6">
        No imports yet. Paste a Kinopoisk ID to start.
      </div>

      <div v-if="store.hasMoreImports" ref="sentinel" class="list-sentinel" />
      <div v-if="store.isLoadingMore" class="flex justify-center q-py-md">
        <q-spinner color="primary" />
      </div>
    </q-card>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import type { QInput } from 'quasar'
import { useMovieImportStore } from 'src/stores/modules/movieImportStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'
import { parseKinopoiskId } from 'src/utils/kinopoisk'
import {
  MovieImportStatusEnum,
  MOVIE_IMPORT_STATUS_LABELS
} from 'src/enums/Movie/MovieImportStatusEnum'
import { IMovie, IMovieImport } from 'src/types/Movie'

const store = useMovieImportStore()
const kpInput = ref('')
const kpInputRef = ref<QInput | null>(null)

const canSubmit = computed(() => (
  Boolean(parseKinopoiskId(kpInput.value)) && !store.isImporting
))

function validateInput(value: string): true | string {
  return parseKinopoiskId(value) ? true : 'Enter a Kinopoisk ID or film/series URL'
}

function poster(movie: IMovie): string | null {
  return moviePosterUrl(movie)
}

function statusColor(status: MovieImportStatusEnum): string {
  if (status === MovieImportStatusEnum.COMPLETED) {
    return 'positive'
  }

  if (status === MovieImportStatusEnum.FAILED) {
    return 'negative'
  }

  return 'grey-7'
}

function durationLabel(item: IMovieImport): string {
  if (item.duration_ms == null) {
    return ''
  }

  const seconds = item.duration_ms / 1000

  return seconds < 10 ? `${seconds.toFixed(1)}s` : `${Math.round(seconds)}s`
}

async function submit(): Promise<void> {
  const isValid = await kpInputRef.value?.validate()
  const kpId = parseKinopoiskId(kpInput.value)

  if (!isValid || !kpId) {
    return
  }

  const imported = await store.importFromKinopoisk(kpId)

  if (imported?.status === MovieImportStatusEnum.COMPLETED) {
    kpInput.value = ''
    kpInputRef.value?.resetValidation()
  }
}

const { sentinel } = useScrollSentinel(
  () => { void store.getImports({ append: true }) },
  () => store.hasMoreImports && !store.isLoading && !store.isLoadingMore
)

onMounted(() => {
  if (!store.imports.length) {
    void store.getImports()
  }
})
</script>

<style lang="scss" scoped>
.import-row {
  min-height: 84px;
}

.list-sentinel {
  height: 1px;
}
</style>
