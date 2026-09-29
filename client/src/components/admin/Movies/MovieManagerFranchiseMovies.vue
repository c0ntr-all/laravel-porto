<template>
  <div class="franchise-movies">
    <div class="franchise-movies__toolbar">
      <q-select
        v-model="pickedMovieId"
        class="franchise-movies__picker"
        dense
        outlined
        use-input
        hide-selected
        fill-input
        emit-value
        map-options
        clearable
        label="Add movie to franchise"
        :options="movieOptions"
        :loading="isSearching"
        @filter="filterMovies"
        @update:model-value="onPickMovie"
      />
    </div>

    <TransitionGroup
      v-if="displayMovies.length"
      ref="listRef"
      tag="div"
      name="franchise-movies"
      class="franchise-movies__list"
      :class="{ 'franchise-movies__list--sorting': Boolean(draggingId) }"
    >
      <div
        v-for="movie in displayMovies"
        :key="movie.id"
        class="franchise-movies__row"
        :class="{ 'franchise-movies__row--dragging': draggingId === movie.id }"
      >
        <q-icon
          class="franchise-movies__handle"
          name="drag_indicator"
          @pointerdown="onPointerDown($event, movie.id)"
        />

        <router-link
          class="franchise-movies__poster"
          :to="{ name: 'movie', params: { id: movie.id } }"
          target="_blank"
          @pointerdown.stop
        >
          <q-img
            v-if="moviePosterUrl(movie)"
            :src="moviePosterUrl(movie) ?? ''"
            :alt="movie.title"
            fit="cover"
          />
          <div v-else class="franchise-movies__poster-fallback">
            <q-icon name="movie" />
          </div>
        </router-link>

        <div class="franchise-movies__info">
          <div class="franchise-movies__title">{{ movie.title }}</div>
          <div class="franchise-movies__meta">
            <span v-if="movie.year">{{ movie.year }}</span>
            <span v-if="movie.kp_rating != null"> · {{ movie.kp_rating.toFixed(1) }}</span>
          </div>
        </div>

        <q-btn
          icon="close"
          flat
          round
          dense
          color="negative"
          :loading="store.isMoviesSaving"
          @click="store.detachMovie(franchiseId, movie.id)"
          @pointerdown.stop
        />
      </div>
    </TransitionGroup>

    <div v-else class="text-grey-6 q-py-md">
      No movies in this franchise yet.
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import type { ComponentPublicInstance } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMoviesResponse, moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'
import { useMovieFranchiseStore } from 'src/stores/modules/movieFranchiseStore'
import { IMovieFranchiseMovie } from 'src/types/Movie'

const DRAG_ACTIVATION_PX = 8

const props = defineProps<{
  franchiseId: string
  movies: IMovieFranchiseMovie[]
}>()

const store = useMovieFranchiseStore()
const pickedMovieId = ref<string | null>(null)
const movieOptions = ref<Array<{ label: string, value: string }>>([])
const isSearching = ref(false)
const listRef = ref<ComponentPublicInstance | HTMLElement | null>(null)
const draggingId = ref<string | null>(null)
const draftMovies = ref<IMovieFranchiseMovie[] | null>(null)
const originMovies = ref<IMovieFranchiseMovie[]>([])
const dragStartY = ref(0)
const dragActivated = ref(false)
let searchTimer: ReturnType<typeof setTimeout> | null = null

const displayMovies = computed(() => draftMovies.value ?? props.movies)

function getListEl(): HTMLElement | null {
  const value = listRef.value

  if (!value) {
    return null
  }

  if (value instanceof HTMLElement) {
    return value
  }

  return (value.$el as HTMLElement | undefined) ?? null
}

function sameOrder(left: IMovieFranchiseMovie[], right: IMovieFranchiseMovie[]): boolean {
  if (left.length !== right.length) {
    return false
  }

  return left.every((item, index) => item.id === right[index]?.id)
}

function moveDraftItem(fromIndex: number, toIndex: number): void {
  if (!draftMovies.value || fromIndex === toIndex || fromIndex < 0 || toIndex < 0) {
    return
  }

  const next = [...draftMovies.value]
  const [item] = next.splice(fromIndex, 1)

  if (!item) {
    return
  }

  next.splice(toIndex, 0, item)
  draftMovies.value = next
}

/**
 * Directional midpoint check: only move up past items above, or down past items below.
 * Staying over the dragged row keeps the current index — no oscillation.
 */
function resolveTargetIndex(clientY: number, fromIndex: number): number {
  const list = getListEl()

  if (!list || !draftMovies.value?.length) {
    return fromIndex
  }

  const rows = Array.from(list.querySelectorAll<HTMLElement>('.franchise-movies__row'))
  let targetIndex = fromIndex

  for (let index = 0; index < rows.length; index += 1) {
    if (index === fromIndex) {
      continue
    }

    const row = rows[index]

    if (!row) {
      continue
    }

    const rect = row.getBoundingClientRect()
    const midpoint = rect.top + rect.height / 2

    if (index < fromIndex && clientY < midpoint) {
      targetIndex = index
      break
    }

    if (index > fromIndex && clientY > midpoint) {
      targetIndex = index
    }
  }

  return targetIndex
}

function onPointerDown(event: PointerEvent, movieId: string): void {
  if (event.button !== 0 || store.isMoviesSaving) {
    return
  }

  event.preventDefault()
  event.stopPropagation()

  draggingId.value = movieId
  dragStartY.value = event.clientY
  dragActivated.value = false
  originMovies.value = props.movies.map(item => ({ ...item }))
  draftMovies.value = props.movies.map(item => ({ ...item }))

  const handle = event.currentTarget as HTMLElement | null
  handle?.setPointerCapture?.(event.pointerId)

  window.addEventListener('pointermove', onPointerMove)
  window.addEventListener('pointerup', onPointerUp)
  window.addEventListener('pointercancel', onPointerUp)
}

function onPointerMove(event: PointerEvent): void {
  if (!draggingId.value || !draftMovies.value) {
    return
  }

  event.preventDefault()

  if (!dragActivated.value) {
    if (Math.abs(event.clientY - dragStartY.value) < DRAG_ACTIVATION_PX) {
      return
    }

    dragActivated.value = true
  }

  const fromIndex = draftMovies.value.findIndex(item => item.id === draggingId.value)

  if (fromIndex === -1) {
    return
  }

  const toIndex = resolveTargetIndex(event.clientY, fromIndex)

  if (fromIndex === toIndex) {
    return
  }

  moveDraftItem(fromIndex, toIndex)
}

async function onPointerUp(): Promise<void> {
  if (!draggingId.value || !draftMovies.value) {
    cleanupDrag()
    return
  }

  const orderedIds = draftMovies.value.map(item => item.id)
  const changed = dragActivated.value && !sameOrder(draftMovies.value, originMovies.value)

  draggingId.value = null
  dragActivated.value = false
  window.removeEventListener('pointermove', onPointerMove)
  window.removeEventListener('pointerup', onPointerUp)
  window.removeEventListener('pointercancel', onPointerUp)

  if (changed) {
    await store.reorderFranchiseMovies(props.franchiseId, orderedIds)
  }

  draftMovies.value = null
  originMovies.value = []
}

function cleanupDrag(): void {
  draggingId.value = null
  draftMovies.value = null
  originMovies.value = []
  dragActivated.value = false
  window.removeEventListener('pointermove', onPointerMove)
  window.removeEventListener('pointerup', onPointerUp)
  window.removeEventListener('pointercancel', onPointerUp)
}

async function filterMovies(
  value: string,
  update: (callback: () => void) => void
): Promise<void> {
  if (searchTimer) {
    clearTimeout(searchTimer)
  }

  searchTimer = setTimeout(async () => {
    isSearching.value = true

    try {
      const response = await movieApi.getMovies({
        title: value.trim() || undefined
      })
      const mapped = mapMoviesResponse(response)
      const attached = new Set(props.movies.map(item => item.id))

      update(() => {
        movieOptions.value = mapped
          .filter(item => !attached.has(item.id))
          .slice(0, 20)
          .map(item => ({
            label: item.year ? `${item.title} (${item.year})` : item.title,
            value: item.id
          }))
      })
    } finally {
      isSearching.value = false
    }
  }, 250)
}

async function onPickMovie(movieId: string | null): Promise<void> {
  if (!movieId) {
    return
  }

  const ok = await store.attachMovie(props.franchiseId, movieId)

  if (ok) {
    pickedMovieId.value = null
    movieOptions.value = []
  }
}

onBeforeUnmount(() => {
  cleanupDrag()

  if (searchTimer) {
    clearTimeout(searchTimer)
  }
})
</script>

<style lang="scss" scoped>
.franchise-movies {
  &__toolbar {
    margin-bottom: 12px;
  }

  &__picker {
    max-width: 420px;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 8px;

    &--sorting {
      user-select: none;
      touch-action: none;
      cursor: grabbing;
    }
  }

  &__row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border: 1px solid rgba(40, 47, 83, 0.08);
    border-radius: 12px;
    background: #fff;
    transition: background-color 0.15s ease, box-shadow 0.15s ease;

    &--dragging {
      position: relative;
      z-index: 1;
      background: rgba(108, 95, 252, 0.06);
      box-shadow: 0 8px 20px rgba(40, 47, 83, 0.12);
    }
  }

  &__handle {
    color: #9aa0b8;
    cursor: grab;
    touch-action: none;

    &:active {
      cursor: grabbing;
    }
  }

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

  &__info {
    flex: 1;
    min-width: 0;
  }

  &__title {
    font-size: 14px;
    font-weight: 600;
    color: #282f53;
  }

  &__meta {
    margin-top: 2px;
    font-size: 13px;
    color: #777a8f;
  }
}

.franchise-movies-move {
  transition: transform 0.22s ease;
}

.franchise-movies__row--dragging.franchise-movies-move {
  transition: none;
}
</style>
