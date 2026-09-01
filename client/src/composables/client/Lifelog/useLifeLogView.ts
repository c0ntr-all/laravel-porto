import { ref } from 'vue'
import { LifeLogViewModeEnum } from 'src/enums/LifeLog/LifeLogViewModeEnum'
import { LifeLogViewMode } from 'src/types'

export function useLifeLogView() {
  const viewMode = ref<LifeLogViewMode>(LifeLogViewModeEnum.Expanded)
  const expandedPostIds = ref<Set<string>>(new Set())
  const showTimeline = ref(true)

  function setViewMode(mode: LifeLogViewMode) {
    viewMode.value = mode
    if (mode === LifeLogViewModeEnum.Expanded) {
      expandedPostIds.value = new Set()
    }
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
    isPostExpanded,
    expandPost,
    collapsePost,
    togglePostExpanded
  }
}
