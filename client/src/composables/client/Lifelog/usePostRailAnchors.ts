import { nextTick, ref, watch, watchEffect, type ComponentPublicInstance, type Ref } from 'vue'
import { IPost } from 'src/types'

export function usePostRailAnchors(posts: Ref<IPost[]>) {
  const containerRef = ref<HTMLElement | null>(null)
  const itemsRef = ref<HTMLElement | null>(null)
  const anchors = ref<Record<string, number>>({})
  const containerHeight = ref(0)
  const postRefs = new Map<string, HTMLElement>()

  function setPostRef(postId: string, element: Element | ComponentPublicInstance | null) {
    const el = element instanceof HTMLElement
      ? element
      : element && '$el' in element && element.$el instanceof HTMLElement
        ? element.$el
        : null

    if (el) {
      postRefs.set(postId, el)
      return
    }

    postRefs.delete(postId)
  }

  function measure() {
    const container = containerRef.value
    const items = itemsRef.value

    if (!container || !items) {
      anchors.value = {}
      containerHeight.value = 0
      return
    }

    const containerRect = container.getBoundingClientRect()
    const nextAnchors: Record<string, number> = {}

    posts.value.forEach(post => {
      const element = postRefs.get(post.id)
      if (!element) {
        return
      }

      const rect = element.getBoundingClientRect()
      nextAnchors[post.id] = rect.top - containerRect.top + rect.height / 2
    })

    anchors.value = nextAnchors
    // Height of the posts column only — never the full grid (rails + sentinel), or rails stretch in a loop.
    containerHeight.value = items.offsetHeight
  }

  watchEffect(onCleanup => {
    const items = itemsRef.value
    if (!items) {
      return
    }

    const resizeObserver = new ResizeObserver(() => {
      measure()
    })

    resizeObserver.observe(items)
    measure()

    onCleanup(() => {
      resizeObserver.disconnect()
    })
  })

  watch(
    posts,
    () => {
      void nextTick(measure)
    },
    { deep: true }
  )

  return {
    containerRef,
    itemsRef,
    anchors,
    containerHeight,
    setPostRef,
    measure
  }
}
