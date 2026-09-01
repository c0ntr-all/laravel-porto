import { computed, ref } from 'vue'
import { LifeLogViewModeEnum } from 'src/enums/LifeLog/LifeLogViewModeEnum'
import { LifeLogViewMode } from 'src/types/LifeLog/filter'
import { useSettingsStore } from 'src/stores/modules/settingsStore'

export function useLifeLogView() {
  const settingsStore = useSettingsStore()
  const viewMode = computed(() => settingsStore.settings.lifelog.defaultViewMode)
  const showTimeline = computed(() => settingsStore.settings.lifelog.showTimeline)
  const expandedPostIds = ref<Set<string>>(new Set())

  function setViewMode(mode: LifeLogViewMode) {
    settingsStore.updateLifelog({ defaultViewMode: mode })
    if (mode === LifeLogViewModeEnum.Expanded) {
      expandedPostIds.value = new Set()
    }
  }

  function setShowTimeline(value: boolean) {
    settingsStore.updateLifelog({ showTimeline: value })
  }

  function isPostExpanded(postId: string): boolean {
    return expandedPostIds.value.has(postId)
  }

  function expandPost(postId: string) {
    const next = new Set(expandedPostIds.value)
    next.add(postId)
    expandedPostIds.value = next
  }

  function collapsePost(postId: string) {
    const next = new Set(expandedPostIds.value)
    next.delete(postId)
    expandedPostIds.value = next
  }

  function togglePostExpanded(postId: string) {
    if (isPostExpanded(postId)) {
      collapsePost(postId)
      return
    }

    expandPost(postId)
  }

  return {
    viewMode,
    expandedPostIds,
    showTimeline,
    setViewMode,
    setShowTimeline,
    isPostExpanded,
    expandPost,
    collapsePost,
    togglePostExpanded
  }
}
