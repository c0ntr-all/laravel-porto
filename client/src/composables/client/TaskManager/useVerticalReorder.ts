import { computed, nextTick, onBeforeUnmount, ref, type Ref } from 'vue'

const LIST_GAP_PX = 8
const MOVE_MS = 220

interface RowMetric {
  top: number
  height: number
}

interface UseVerticalReorderOptions<T extends { id: string }> {
  items: Ref<T[]>
  rowSelector: string
  onReorder: (orderedIds: string[]) => Promise<boolean | void>
}

export function useVerticalReorder<T extends { id: string }> (
  listRef: Ref<HTMLElement | null>,
  options: UseVerticalReorderOptions<T>
) {
  const draggingId = ref<string | null>(null)
  const originItems = ref<T[]>([]) as Ref<T[]>
  const orderedPreview = ref<T[] | null>(null)
  const originIndex = ref(-1)
  const insertIndex = ref(-1)
  const pointerDelta = ref(0)
  const dragActive = ref(false)
  const settling = ref(false)
  const rowMetrics = ref<RowMetric[]>([])
  const rowGap = ref(LIST_GAP_PX)
  const pointerOriginY = ref(0)
  let dragSession = 0

  const displayItems = computed(() => {
    if (orderedPreview.value) return orderedPreview.value
    if (draggingId.value && originItems.value.length) return originItems.value
    return options.items.value
  })

  const isSorting = computed(() => Boolean(draggingId.value))

  function shiftY (index: number): number {
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

  function rowStyle (index: number): Record<string, string> | undefined {
    if (!draggingId.value) return undefined

    const isDragged = index === originIndex.value

    return {
      transform: `translateY(${shiftY(index)}px)`,
      transition: isDragged && !settling.value ? 'none' : `transform ${MOVE_MS}ms ease`,
      zIndex: isDragged ? '3' : '1'
    }
  }

  function measureRows (): void {
    const list = listRef.value
    if (!list) {
      rowMetrics.value = []
      return
    }

    const styles = getComputedStyle(list)
    const parsedGap = Number.parseFloat(styles.rowGap || styles.gap)
    rowGap.value = Number.isFinite(parsedGap) ? parsedGap : LIST_GAP_PX

    const listTop = list.getBoundingClientRect().top
    rowMetrics.value = Array.from(list.querySelectorAll<HTMLElement>(options.rowSelector)).map((row) => {
      const rect = row.getBoundingClientRect()
      return {
        top: rect.top - listTop,
        height: rect.height
      }
    })
  }

  function resolveInsertIndex (delta: number): number {
    const from = originIndex.value
    const metrics = rowMetrics.value
    const current = metrics[from]
    if (!current) return from

    const center = current.top + delta + current.height / 2
    let index = from

    if (delta > 0) {
      for (let i = from + 1; i < metrics.length; i += 1) {
        const row = metrics[i]
        if (!row) continue
        if (center > row.top + row.height / 2) index = i
        else break
      }
    } else if (delta < 0) {
      for (let i = from - 1; i >= 0; i -= 1) {
        const row = metrics[i]
        if (!row) continue
        if (center < row.top + row.height / 2) index = i
        else break
      }
    }

    return index
  }

  function targetDelta (from: number, to: number): number {
    const origin = rowMetrics.value[from]
    const target = rowMetrics.value[to]
    if (!origin || !target || from === to) return 0

    if (to > from) {
      return (target.top + target.height) - (origin.top + origin.height)
    }

    return target.top - origin.top
  }

  function reorderList (items: T[], from: number, to: number): T[] {
    const next = [...items]
    const [item] = next.splice(from, 1)
    if (!item) return next
    next.splice(to, 0, item)
    return next
  }

  function resetDragVisual (): void {
    draggingId.value = null
    originIndex.value = -1
    insertIndex.value = -1
    pointerDelta.value = 0
    dragActive.value = false
    settling.value = false
    rowMetrics.value = []
    originItems.value = []
    pointerOriginY.value = 0
  }

  function onPointerMove (event: PointerEvent): void {
    if (!draggingId.value || settling.value || originIndex.value < 0) return

    event.preventDefault()
    dragActive.value = true
    pointerDelta.value = event.clientY - pointerOriginY.value
    insertIndex.value = resolveInsertIndex(pointerDelta.value)
  }

  async function finishDrag (session: number): Promise<void> {
    const from = originIndex.value
    const to = insertIndex.value
    const changed = from >= 0 && to >= 0 && from !== to
    const snapshot = [...originItems.value]

    window.removeEventListener('pointermove', onPointerMove)
    window.removeEventListener('pointerup', onPointerUp)
    window.removeEventListener('pointercancel', onPointerUp)

    if (draggingId.value && (changed || pointerDelta.value !== 0)) {
      settling.value = true
      await nextTick()
      await new Promise<void>(resolve => { requestAnimationFrame(() => resolve()) })

      if (session !== dragSession) return

      pointerDelta.value = changed ? targetDelta(from, to) : 0
      await new Promise<void>(resolve => { window.setTimeout(resolve, MOVE_MS) })
    }

    if (session !== dragSession) return

    if (changed) {
      orderedPreview.value = reorderList(snapshot, from, to)
    }

    resetDragVisual()

    if (!changed) return

    try {
      const orderedIds = (orderedPreview.value ?? snapshot).map(item => item.id)
      await options.onReorder(orderedIds)
    } finally {
      orderedPreview.value = null
    }
  }

  function onPointerUp (): void {
    void finishDrag(dragSession)
  }

  function onPointerDown (event: PointerEvent, itemId: string, index: number): void {
    if (event.button !== 0 || settling.value || options.items.value.length < 2) return

    event.preventDefault()
    event.stopPropagation()

    dragSession += 1
    pointerOriginY.value = event.clientY
    originItems.value = [...options.items.value]
    draggingId.value = itemId
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

  function cleanupDrag (): void {
    dragSession += 1
    orderedPreview.value = null
    resetDragVisual()
    window.removeEventListener('pointermove', onPointerMove)
    window.removeEventListener('pointerup', onPointerUp)
    window.removeEventListener('pointercancel', onPointerUp)
  }

  onBeforeUnmount(() => {
    cleanupDrag()
  })

  return {
    displayItems,
    isSorting,
    draggingId,
    dragActive,
    rowStyle,
    onPointerDown,
    cleanupDrag
  }
}
