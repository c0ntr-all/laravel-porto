import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { usePostStore } from 'src/stores/modules/postStore'
import { useTagStore } from 'src/stores/modules/tagStore'
import { usePresetStore } from 'src/stores/modules/presetStore'
import {
  ILifeLogFilter,
  IPreset
} from 'src/types'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import {
  applyClientSidePostFilter,
  mapLifeLogFilterToApiFilter,
  mapPresetToLifeLogFilter
} from 'src/utils/LifeLog/filter.mapper'

export function useLifeLogFilters() {
  const postStore = usePostStore()
  const tagStore = useTagStore()
  const presetStore = usePresetStore()

  const { posts, isLoading } = storeToRefs(postStore)
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

  async function loadPosts() {
    await postStore.getPosts(mapLifeLogFilterToApiFilter(filter.value))
  }

  async function applyFilter(nextFilter: ILifeLogFilter) {
    filter.value = { ...nextFilter }
    await loadPosts()
  }

  async function resetFilter() {
    filter.value = createEmptyLifeLogFilter()
    await loadPosts()
  }

  async function applyPreset(preset: IPreset) {
    const nextFilter = mapPresetToLifeLogFilter(preset, allTags.value)
    await applyFilter(nextFilter)
  }

  async function clearPreset() {
    filter.value = {
      ...filter.value,
      activePresetId: null
    }
    await loadPosts()
  }

  return {
    filter,
    allTags,
    filteredPosts,
    activePreset,
    isLoading,
    loadTags,
    loadPresets,
    loadPosts,
    applyFilter,
    resetFilter,
    applyPreset,
    clearPreset
  }
}
