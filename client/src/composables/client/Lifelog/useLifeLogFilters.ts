import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { usePostStore } from 'src/stores/modules/postStore'
import { useTagStore } from 'src/stores/modules/tagStore'
import { usePresetStore } from 'src/stores/modules/presetStore'
import {
  ILifeLogFilter,
  IPreset
} from 'src/types'
import {
  cloneLifeLogFilter,
  createEmptyLifeLogFilter
} from 'src/utils/LifeLog/filter'
import {
  applyClientSidePostFilter,
  mapLifeLogFilterToApiFilter,
  mapPresetToLifeLogFilter
} from 'src/utils/LifeLog/filter.mapper'
import { hasActiveLifeLogFilter } from 'src/utils/LifeLog/filter.url'
import { useLifeLogFilterRoute } from 'src/composables/client/Lifelog/useLifeLogFilterRoute'

interface ApplyFilterOptions {
  syncRoute?: boolean
}

export function useLifeLogFilters() {
  const postStore = usePostStore()
  const tagStore = useTagStore()
  const presetStore = usePresetStore()

  const { posts, isLoading, isLoadingMore, hasMorePosts } = storeToRefs(postStore)
  const { presets } = storeToRefs(presetStore)

  const filter = ref<ILifeLogFilter>(createEmptyLifeLogFilter())
  const allTags = ref<Awaited<ReturnType<typeof tagStore.getTags>>>([])

  const filteredPosts = computed(() =>
    applyClientSidePostFilter(posts.value, filter.value)
  )

  const activePreset = computed(() =>
    presets.value.find(preset => preset.id === filter.value.activePresetId) ?? null
  )

  async function loadTags() {
    const tags = await tagStore.getTags()
    if (tags) {
      allTags.value = [...tags]
    }
  }

  async function loadPresets() {
    await presetStore.getPresets()
  }

  async function loadPosts(append = false) {
    await postStore.getPosts({
      append,
      filters: mapLifeLogFilterToApiFilter(filter.value)
    })
  }

  async function loadMorePosts() {
    await loadPosts(true)
  }

  async function applyFilter(
    nextFilter: ILifeLogFilter,
    options: ApplyFilterOptions = {}
  ) {
    const { syncRoute = true } = options

    filter.value = cloneLifeLogFilter(nextFilter)
    await loadPosts()

    if (syncRoute) {
      await filterRoute.syncToRoute(filter.value)
    }
  }

  async function applyFilterFromRoute(nextFilter: ILifeLogFilter) {
    filter.value = cloneLifeLogFilter(nextFilter)
    await loadPosts()
  }

  async function resetFilter() {
    await applyFilter(createEmptyLifeLogFilter())
  }

  async function applyPreset(preset: IPreset) {
    const nextFilter = mapPresetToLifeLogFilter(preset, allTags.value)
    await applyFilter(nextFilter)
  }

  async function clearPreset() {
    await applyFilter({
      ...filter.value,
      activePresetId: null,
      content_types: []
    })
  }

  const filterRoute = useLifeLogFilterRoute({
    allTags,
    presets,
    getFilter: () => filter.value,
    onFilterFromRoute: applyFilterFromRoute
  })

  async function initialize() {
    await Promise.all([
      loadTags(),
      loadPresets()
    ])

    const fromRoute = filterRoute.parseFromRoute()

    if (fromRoute && hasActiveLifeLogFilter(fromRoute)) {
      await applyFilter(fromRoute, { syncRoute: false })
      return
    }

    await loadPosts()
  }

  return {
    filter,
    allTags,
    filteredPosts,
    activePreset,
    isLoading,
    isLoadingMore,
    hasMorePosts,
    initialize,
    loadTags,
    loadPresets,
    loadPosts,
    loadMorePosts,
    applyFilter,
    resetFilter,
    applyPreset,
    clearPreset
  }
}
