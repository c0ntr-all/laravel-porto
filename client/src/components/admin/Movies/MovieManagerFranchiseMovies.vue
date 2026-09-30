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

    <div
      v-if="displayMovies.length"
      ref="listRef"
      class="franchise-movies__list"
      :class="{ 'franchise-movies__list--sorting': Boolean(draggingId) }"
    >
      <div
        v-for="(movie, index) in displayMovies"
        :key="movie.id"
        class="franchise-movies__row"
        :class="{
          'franchise-movies__row--lifted': draggingId === movie.id,
          'franchise-movies__row--active': draggingId === movie.id && dragActive
        }"
        :style="rowStyle(index)"
      >
        <q-icon
          class="franchise-movies__handle"
          name="drag_indicator"
          @pointerdown="onPointerDown($event, movie.id, index)"
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
    </div>

    <div v-else class="text-grey-6 q-py-md">
      No movies in this franchise yet.
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, nextTick, onBeforeUnmount, ref } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMoviesResponse, moviePosterUrl } from 'src/api/mappers/Movie/movie.mapper'
import { useMovieFranchiseStore } from 'src/stores/modules/movieFranchiseStore'
import { IMovieFranchiseMovie } from 'src/types/Movie'

const LIST_GAP_PX = 8
const MOVE_MS = 220

interface RowMetric {
  top: number
  height: number
}

const props = defineProps<{
  franchiseId: string
  movies: IMovieFranchiseMovie[]
}>()

const store = useMovieFranchiseStore()
const pickedMovieId = ref<string | null>(null)
const movieOptions = ref<Array<{ label: string, value: string }>>([])
const isSearching = ref(false)
const listRef = ref<HTMLElement | null>(null)
const draggingId = ref<string | null>(null)
const originMovies = ref<IMovieFranchiseMovie[]>([])
const orderedPreview = ref<IMovieFranchiseMovie[] | null>(null)
const originIndex = ref(-1)
const insertIndex = ref(-1)
const pointerDelta = ref(0)
const dragActive = ref(false)
const settling = ref(false)
const rowMetrics = ref<RowMetric[]>([])
const rowGap = ref(LIST_GAP_PX)
const pointerOriginY = ref(0)
let searchTimer: ReturnType<typeof setTimeout> | null = null
let dragSession = 0

const displayMovies = computed(() => {
  if (orderedPreview.value) {
    return orderedPreview.value
  }

  if (draggingId.value && originMovies.value.length) {
    return originMovies.value
  }

  return props.movies
})

function shiftY(index: number): number {
  const from = originIndex.value
  const to = insertIndex.value

  if (!draggingId.value || from < 0 || !rowMetrics.value[from]) {
    return 0
  }

  if (index === from) {
    return pointerDelta.value
  }

  const slot = rowMetrics.value[from].height + rowGap.value

  if (to > from && index > from && index <= to) {
    return -slot
  }

  if (to < from && index >= to && index < from) {
    return slot
  }

  return 0
}

function rowStyle(index: number): Record<string, string> | undefined {
  if (!draggingId.value) {
    return undefined
  }

  const isDragged = index === originIndex.value

  return {
    transform: `translateY(${shiftY(index)}px)`,
    transition: isDragged && !settling.value ? 'none' : `transform ${MOVE_MS}ms ease`,
    zIndex: isDragged ? '3' : '1'
  }
}

function measureRows(): void {
  const list = listRef.value

  if (!list) {
    rowMetrics.value = []
    return
  }

  const styles = getComputedStyle(list)
  const parsedGap = Number.parseFloat(styles.rowGap || styles.gap)

  rowGap.value = Number.isFinite(parsedGap) ? parsedGap : LIST_GAP_PX

  const listTop = list.getBoundingClientRect().top

  rowMetrics.value = Array.from(list.querySelectorAll<HTMLElement>('.franchise-movies__row')).map((row) => {
    const rect = row.getBoundingClientRect()

    return {
      top: rect.top - listTop,
      height: rect.height
    }
  })
}

function resolveInsertIndex(delta: number): number {
  const from = originIndex.value
  const metrics = rowMetrics.value
  const current = metrics[from]

  if (!current) {
    return from
  }

  const center = current.top + delta + current.height / 2
  let index = from

  if (delta > 0) {
    for (let i = from + 1; i < metrics.length; i += 1) {
      const row = metrics[i]

      if (!row) {
        continue
      }

      const midpoint = row.top + row.height / 2

      if (center > midpoint) {
        index = i
      } else {
        break
      }
    }
  } else if (delta < 0) {
    for (let i = from - 1; i >= 0; i -= 1) {
      const row = metrics[i]

      if (!row) {
        continue
      }

      const midpoint = row.top + row.height / 2

      if (center < midpoint) {
        index = i
      } else {
        break
      }
    }
  }

  return index
}

function targetDelta(from: number, to: number): number {
  const metrics = rowMetrics.value
  const origin = metrics[from]
  const target = metrics[to]

  if (!origin || !target || from === to) {
    return 0
  }

  if (to > from) {
    return (target.top + target.height) - (origin.top + origin.height)
  }

  return target.top - origin.top
}

function reorderList(
  items: IMovieFranchiseMovie[],
  from: number,
  to: number
): IMovieFranchiseMovie[] {
  const next = items.map(item => ({ ...item }))
  const [item] = next.splice(from, 1)

  if (!item) {
    return next
  }

  next.splice(to, 0, item)

  return next
}

function resetDragVisual(): void {
  draggingId.value = null
  originIndex.value = -1
  insertIndex.value = -1
  pointerDelta.value = 0
  dragActive.value = false
  settling.value = false
  rowMetrics.value = []
  originMovies.value = []
  pointerOriginY.value = 0
}

function onPointerDown(event: PointerEvent, movieId: string, index: number): void {
  if (event.button !== 0 || store.isMoviesSaving || settling.value) {
    return
  }

  event.preventDefault()
  event.stopPropagation()

  dragSession += 1
  pointerOriginY.value = event.clientY
  originMovies.value = props.movies.map(item => ({ ...item }))
  draggingId.value = movieId
  originIndex.value = index
  insertIndex.value = index
  pointerDelta.value = 0
  dragActive.value = false
  settling.value = false

  measureRows()

  const handle = event.currentTarget as HTMLElement | null
  handle?.setPointerCapture?.(event.pointerId)

  window.addEventListener('pointermove', onPointerMove)
  window.addEventListener('pointerup', onPointerUp)
  window.addEventListener('pointercancel', onPointerUp)
}

function onPointerMove(event: PointerEvent): void {
  if (!draggingId.value || settling.value || originIndex.value < 0) {
    return
  }

  event.preventDefault()
  dragActive.value = true
  pointerDelta.value = event.clientY - pointerOriginY.value
  insertIndex.value = resolveInsertIndex(pointerDelta.value)
}

async function finishDrag(session: number): Promise<void> {
  const from = originIndex.value
  const to = insertIndex.value
  const changed = from >= 0 && to >= 0 && from !== to
  const snapshot = originMovies.value.map(item => ({ ...item }))

  window.removeEventListener('pointermove', onPointerMove)
  window.removeEventListener('pointerup', onPointerUp)
  window.removeEventListener('pointercancel', onPointerUp)

  if (draggingId.value && (changed || pointerDelta.value !== 0)) {
    settling.value = true
    await nextTick()
    await new Promise<void>(resolve => { requestAnimationFrame(() => resolve()) })

    if (session !== dragSession) {
      return
    }

    pointerDelta.value = changed ? targetDelta(from, to) : 0
    await new Promise<void>(resolve => { window.setTimeout(resolve, MOVE_MS) })
  }

  if (session !== dragSession) {
    return
  }

  if (changed) {
    orderedPreview.value = reorderList(snapshot, from, to)
  }

  resetDragVisual()

  if (!changed) {
    return
  }

  try {
    await store.reorderFranchiseMovies(
      props.franchiseId,
      (orderedPreview.value ?? snapshot).map(item => item.id)
    )
  } finally {
    orderedPreview.value = null
  }
}

function onPointerUp(): void {
  const session = dragSession
  void finishDrag(session)
}

function cleanupDrag(): void {
  dragSession += 1
  orderedPreview.value = null
  resetDragVisual()
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

    &--lifted {
      position: relative;
      z-index: 3;
      background: #fff;
    }

    &--active {
      box-shadow: 0 14px 28px rgba(40, 47, 83, 0.18);
      cursor: grabbing;
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
</style>
