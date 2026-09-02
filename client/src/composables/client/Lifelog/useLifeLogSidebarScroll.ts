import { onMounted, onUnmounted, type Ref } from 'vue'

export function useLifeLogSidebarScroll(
  sidebarRef: Ref<HTMLElement | null>,
  contentRef: Ref<HTMLElement | null>
) {
  function syncSidebarScroll() {
    const sidebar = sidebarRef.value
    const content = contentRef.value

    if (!sidebar || !content) {
      return
    }

    const maxSidebarScroll = sidebar.scrollHeight - sidebar.clientHeight
    if (maxSidebarScroll <= 0) {
      sidebar.scrollTop = 0
      return
    }

    const contentRect = content.getBoundingClientRect()
    const stickyOffset = 12
    const viewportBottom = window.innerHeight
    const visibleContentHeight = Math.max(viewportBottom - stickyOffset, 1)
    const contentScrollable = Math.max(contentRect.height - visibleContentHeight, 1)
    const scrolled = Math.min(Math.max(stickyOffset - contentRect.top, 0), contentScrollable)
    const progress = scrolled / contentScrollable

    sidebar.scrollTop = progress * maxSidebarScroll
  }

  onMounted(() => {
    window.addEventListener('scroll', syncSidebarScroll, { passive: true })
    window.addEventListener('resize', syncSidebarScroll, { passive: true })
    syncSidebarScroll()
  })

  onUnmounted(() => {
    window.removeEventListener('scroll', syncSidebarScroll)
    window.removeEventListener('resize', syncSidebarScroll)
  })

  return {
    syncSidebarScroll
  }
}
