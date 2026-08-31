import { onUnmounted, ref, watch, type Ref } from 'vue'

export function useScrollSentinel (
  onIntersect: () => void,
  canLoad: () => boolean
): { sentinel: Ref<HTMLElement | null> } {
  const sentinel = ref<HTMLElement | null>(null)
  let observer: IntersectionObserver | null = null

  const disconnect = () => {
    observer?.disconnect()
    observer = null
  }

  const observe = () => {
    disconnect()

    if (!sentinel.value) {
      return
    }

    observer = new IntersectionObserver((entries) => {
      if (!entries.some(entry => entry.isIntersecting)) {
        return
      }

      if (!canLoad()) {
        return
      }

      onIntersect()
    }, {
      root: null,
      rootMargin: '320px 0px',
      threshold: 0
    })

    observer.observe(sentinel.value)
  }

  watch(sentinel, observe, { flush: 'post' })
  onUnmounted(disconnect)

  return { sentinel }
}
