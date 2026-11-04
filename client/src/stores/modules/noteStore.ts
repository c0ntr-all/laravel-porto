import { defineStore } from 'pinia'
import { computed, reactive, ref } from 'vue'
import axios from 'axios'
import { noteApi } from 'src/api/requests/noteApi'
import { mapNoteResponse, mapNotesResponse } from 'src/api/mappers/Notes/note.mapper'
import {
  extractCursorFromResponse,
  handleApiError,
  hasMoreFromResponse,
  upsertEntity
} from 'src/utils/jsonapi'
import { INote, INoteCreatePayload, INoteUpdatePayload } from 'src/types'
import { StoreEntity } from 'src/types/store'

const PAGE_SIZE = 20
const PINNED_PAGE_SIZE = 50

function mergeIds (current: string[], incoming: string[]): string[] {
  const seen = new Set(current)
  const next = [...current]

  incoming.forEach((id) => {
    if (seen.has(id)) return
    seen.add(id)
    next.push(id)
  })

  return next
}

function conflictMessageFrom (error: unknown): string | null {
  if (!axios.isAxiosError(error) || error.response?.status !== 409) {
    return null
  }

  const data = error.response.data
  if (!data || typeof data !== 'object') return null

  const message = (data as { message?: unknown }).message
  return typeof message === 'string' && message !== '' ? message : null
}

export const useNoteStore = defineStore('note', () => {
  const notes = reactive<StoreEntity<INote>>({ byId: {}, allIds: [] })

  const rootIds = ref<string[]>([])
  const rootCursor = ref<string | null>(null)
  const rootHasMore = ref(false)
  const isRootsLoading = ref(false)
  const isRootsLoadingMore = ref(false)
  const rootsLoaded = ref(false)

  const childrenIds = reactive<Record<string, string[]>>({})
  const childrenCursor = reactive<Record<string, string | null>>({})
  const childrenHasMore = reactive<Record<string, boolean>>({})
  const childLoading = reactive<Record<string, boolean>>({})
  const expanded = reactive<Record<string, boolean>>({})

  const pinnedIds = ref<string[]>([])
  const isPinnedLoading = ref(false)
  const pinnedLoaded = ref(false)

  const searchText = ref('')
  const searchIds = ref<string[]>([])
  const searchCursor = ref<string | null>(null)
  const searchHasMore = ref(false)
  const isSearchLoading = ref(false)
  const isSearchLoadingMore = ref(false)

  const current = ref<INote | null>(null)
  const isCurrentLoading = ref(false)
  const isSaving = ref(false)
  const conflictMessage = ref<string | null>(null)

  const isSearchActive = computed(() => searchText.value.trim() !== '')
  const listReady = computed(() => rootsLoaded.value && pinnedLoaded.value)

  let rootToken = 0
  let searchToken = 0
  let pinnedToken = 0
  let openToken = 0
  const childTokens: Record<string, number> = {}

  function remember (note: INote): void {
    upsertEntity(notes, note)
  }

  function detach (id: string): void {
    rootIds.value = rootIds.value.filter(item => item !== id)
    Object.keys(childrenIds).forEach((key) => {
      if (!childrenIds[key]?.includes(id)) return
      childrenIds[key] = childrenIds[key].filter(item => item !== id)
    })
  }

  function forget (id: string): void {
    delete notes.byId[id]
    notes.allIds = notes.allIds.filter(item => item !== id)
    detach(id)
    pinnedIds.value = pinnedIds.value.filter(item => item !== id)
    delete childrenIds[id]
    delete childrenCursor[id]
    delete childrenHasMore[id]
    delete childLoading[id]
    delete expanded[id]
    delete childTokens[id]
  }

  function ensurePlaced (note: INote): void {
    detach(note.id)

    if (note.parent_id == null) {
      rootIds.value.unshift(note.id)
      return
    }

    if (childrenIds[note.parent_id]) {
      childrenIds[note.parent_id].unshift(note.id)
    }
  }

  function patchOpenPin (note: INote): void {
    if (current.value?.id !== note.id) return

    current.value = {
      ...current.value,
      is_pinned: note.is_pinned,
      pin_position: note.pin_position
    }
  }

  function syncOpenPinFlags (): void {
    if (!current.value) return
    const fresh = notes.byId[current.value.id]
    if (!fresh) return
    patchOpenPin(fresh)
  }

  async function loadRoots (append = false): Promise<void> {
    let token = rootToken

    if (append) {
      if (!rootHasMore.value || isRootsLoadingMore.value || !rootCursor.value) return
      isRootsLoadingMore.value = true
    } else {
      rootToken += 1
      token = rootToken
      isRootsLoading.value = true
      isRootsLoadingMore.value = false
    }

    try {
      const response = await noteApi.list({
        roots: true,
        cursor: append ? rootCursor.value : null,
        perPage: PAGE_SIZE
      })

      if (token !== rootToken) return

      const items = mapNotesResponse(response)
      items.forEach(remember)
      const ids = items.map(item => item.id)
      rootIds.value = append ? mergeIds(rootIds.value, ids) : ids
      rootCursor.value = extractCursorFromResponse(response)
      rootHasMore.value = hasMoreFromResponse(response)
    } catch (error) {
      if (token !== rootToken) return
      handleApiError(error)
    } finally {
      if (token === rootToken) {
        isRootsLoading.value = false
        isRootsLoadingMore.value = false
        rootsLoaded.value = true
      }
    }
  }

  async function loadChildren (parentId: string, append = false): Promise<void> {
    let token = childTokens[parentId] ?? 0

    if (append) {
      if (!childrenHasMore[parentId] || childLoading[parentId] || !childrenCursor[parentId]) return
    } else {
      token += 1
      childTokens[parentId] = token
    }

    childLoading[parentId] = true

    try {
      const response = await noteApi.list({
        parentId: Number(parentId),
        cursor: append ? childrenCursor[parentId] : null,
        perPage: PAGE_SIZE
      })

      if (childTokens[parentId] !== token) return

      const items = mapNotesResponse(response)
      items.forEach(remember)
      const ids = items.map(item => item.id)
      childrenIds[parentId] = append ? mergeIds(childrenIds[parentId] ?? [], ids) : ids
      childrenCursor[parentId] = extractCursorFromResponse(response)
      childrenHasMore[parentId] = hasMoreFromResponse(response)
    } catch (error) {
      if (childTokens[parentId] !== token) return
      handleApiError(error)
    } finally {
      if (childTokens[parentId] === token) {
        childLoading[parentId] = false
      }
    }
  }

  async function toggleExpanded (id: string): Promise<void> {
    if (expanded[id]) {
      expanded[id] = false
      return
    }

    expanded[id] = true
    const loaded = childrenIds[id]
    const count = notes.byId[id]?.children_count ?? 0
    if (!loaded || (loaded.length === 0 && count > 0)) {
      await loadChildren(id)
    }
  }

  async function loadPinned (): Promise<void> {
    const token = ++pinnedToken
    isPinnedLoading.value = true

    try {
      const ids: string[] = []
      let cursor: string | null = null

      do {
        const response = await noteApi.list({
          pinned: 1,
          sort: 'pin_position',
          perPage: PINNED_PAGE_SIZE,
          cursor
        })

        if (token !== pinnedToken) return

        const items = mapNotesResponse(response)
        items.forEach(remember)
        ids.push(...items.map(item => item.id))
        cursor = hasMoreFromResponse(response) ? extractCursorFromResponse(response) : null
      } while (cursor)

      if (token !== pinnedToken) return

      pinnedIds.value = mergeIds([], ids)
      syncOpenPinFlags()
    } catch (error) {
      if (token !== pinnedToken) return
      handleApiError(error)
    } finally {
      if (token === pinnedToken) {
        isPinnedLoading.value = false
        pinnedLoaded.value = true
      }
    }
  }

  async function search (raw: string, append = false): Promise<void> {
    const term = raw.trim()
    let token = searchToken

    if (!append) {
      searchToken += 1
      token = searchToken
      searchText.value = term
      searchCursor.value = null
      searchHasMore.value = false
    }

    if (!term) {
      searchIds.value = []
      isSearchLoading.value = false
      isSearchLoadingMore.value = false
      return
    }

    if (append) {
      if (!searchCursor.value || isSearchLoadingMore.value) return
      isSearchLoadingMore.value = true
    } else {
      searchIds.value = []
      isSearchLoading.value = true
    }

    try {
      const response = await noteApi.list({
        text: term,
        cursor: append ? searchCursor.value : null,
        perPage: PAGE_SIZE
      })

      if (token !== searchToken) return

      const items = mapNotesResponse(response)
      items.forEach(remember)
      const ids = items.map(item => item.id)
      searchIds.value = append ? mergeIds(searchIds.value, ids) : ids
      searchCursor.value = extractCursorFromResponse(response)
      searchHasMore.value = hasMoreFromResponse(response)
    } catch (error) {
      if (token !== searchToken) return
      handleApiError(error)
    } finally {
      if (token === searchToken) {
        isSearchLoading.value = false
        isSearchLoadingMore.value = false
      }
    }
  }

  async function findNotes (text: string): Promise<INote[]> {
    const term = text.trim()
    if (!term) return []

    try {
      const response = await noteApi.list({ text: term, perPage: PAGE_SIZE })
      const items = mapNotesResponse(response)
      items.forEach(remember)
      return items
    } catch (error) {
      handleApiError(error)
      return []
    }
  }

  async function fetchNote (id: string): Promise<INote | null> {
    try {
      const response = await noteApi.get(id)
      const note = mapNoteResponse(response)
      remember(note)
      return note
    } catch (error) {
      handleApiError(error)
      return null
    }
  }

  function closeNote (): void {
    openToken += 1
    current.value = null
    isCurrentLoading.value = false
    conflictMessage.value = null
  }

  async function openNote (id: string): Promise<INote | null> {
    const token = ++openToken
    isCurrentLoading.value = true

    if (current.value?.id !== id) {
      current.value = null
      conflictMessage.value = null
    }

    try {
      const response = await noteApi.get(id)
      const note = mapNoteResponse(response)
      if (token !== openToken) return note

      remember(note)
      current.value = note
      conflictMessage.value = null
      return note
    } catch (error) {
      if (token !== openToken) return null
      handleApiError(error)
      return null
    } finally {
      if (token === openToken) {
        isCurrentLoading.value = false
      }
    }
  }

  async function createNote (payload: INoteCreatePayload): Promise<INote | null> {
    try {
      const response = await noteApi.create(payload)
      const note = mapNoteResponse(response)
      remember(note)

      if (note.parent_id) {
        expanded[note.parent_id] = true
        await loadChildren(note.parent_id)
        const parent = await fetchNote(note.parent_id)
        if (parent && current.value?.id === parent.id && current.value.updated_at === parent.updated_at) {
          current.value = {
            ...current.value,
            children_count: parent.children_count
          }
        }
      } else {
        rootIds.value = mergeIds([note.id], rootIds.value)
      }

      return note
    } catch (error) {
      handleApiError(error)
      return null
    }
  }

  function showConflict (id: string, error: unknown): boolean {
    const message = conflictMessageFrom(error)
    if (!message) return false

    if (current.value?.id === id) {
      conflictMessage.value = message
    } else {
      handleApiError(new Error(message))
    }

    return true
  }

  async function saveNote (id: string, payload: INoteUpdatePayload): Promise<INote | null> {
    if (!payload.updated_at) {
      handleApiError(new Error('Не удалось прочитать версию заметки'))
      return null
    }

    isSaving.value = true

    try {
      const response = await noteApi.update(id, payload)
      const note = mapNoteResponse(response)
      remember(note)
      if (current.value?.id === id) current.value = note
      conflictMessage.value = null
      return note
    } catch (error) {
      if (showConflict(id, error)) return null
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function moveNote (id: string, parentId: number | null): Promise<INote | null> {
    const source = current.value?.id === id ? current.value : notes.byId[id]
    if (!source?.updated_at) {
      handleApiError(new Error('Не удалось прочитать версию заметки'))
      return null
    }

    const previousParent = source.parent_id

    try {
      const response = await noteApi.update(id, {
        parent_id: parentId,
        updated_at: source.updated_at
      })
      const note = mapNoteResponse(response)
      remember(note)
      if (current.value?.id === id) current.value = note
      conflictMessage.value = null

      if (previousParent) {
        if (expanded[previousParent] || childrenIds[previousParent]) {
          await loadChildren(previousParent)
        }
        await fetchNote(previousParent)
      } else {
        await loadRoots()
      }

      if (note.parent_id) {
        expanded[note.parent_id] = true
        await loadChildren(note.parent_id)
        await fetchNote(note.parent_id)
      } else if (previousParent) {
        await loadRoots()
      }

      ensurePlaced(note)
      return note
    } catch (error) {
      if (showConflict(id, error)) return null
      handleApiError(error)
      return null
    }
  }

  async function removeNote (id: string): Promise<boolean> {
    const existing = notes.byId[id]
    const parentId = existing?.parent_id ?? null
    const childIds = notes.allIds.filter(item => notes.byId[item]?.parent_id === id)

    try {
      await noteApi.remove(id)
    } catch (error) {
      handleApiError(error)
      return false
    }

    const openId = current.value?.id ?? null
    forget(id)
    if (openId === id) {
      current.value = null
      conflictMessage.value = null
    }

    if (parentId) {
      if (expanded[parentId] || childrenIds[parentId]) {
        await loadChildren(parentId)
      }
      const parent = await fetchNote(parentId)
      if (parent && current.value?.id === parent.id && current.value.updated_at === parent.updated_at) {
        current.value = {
          ...current.value,
          children_count: parent.children_count
        }
      }
    } else {
      await loadRoots()
    }

    for (const childId of childIds) {
      const child = await fetchNote(childId)
      if (!child) continue
      ensurePlaced(child)
      if (current.value?.id === child.id) current.value = child
    }

    await loadPinned()
    return true
  }

  async function setPinned (id: string, pinned: boolean): Promise<void> {
    try {
      const response = pinned ? await noteApi.pin(id) : await noteApi.unpin(id)
      const note = mapNoteResponse(response)
      remember(note)
      patchOpenPin(note)
      await loadPinned()
    } catch (error) {
      handleApiError(error)
    }
  }

  async function movePinned (id: string, beforeId: number | null): Promise<boolean> {
    try {
      await noteApi.movePin(id, beforeId)
      await loadPinned()
      return true
    } catch (error) {
      handleApiError(error)
      await loadPinned()
      return false
    }
  }

  return {
    notes,
    rootIds,
    rootHasMore,
    isRootsLoading,
    isRootsLoadingMore,
    rootsLoaded,
    childrenIds,
    childrenHasMore,
    childLoading,
    expanded,
    pinnedIds,
    isPinnedLoading,
    pinnedLoaded,
    searchText,
    searchIds,
    searchHasMore,
    isSearchLoading,
    isSearchLoadingMore,
    isSearchActive,
    listReady,
    current,
    isCurrentLoading,
    isSaving,
    conflictMessage,
    loadRoots,
    loadChildren,
    toggleExpanded,
    loadPinned,
    search,
    findNotes,
    closeNote,
    openNote,
    createNote,
    saveNote,
    moveNote,
    removeNote,
    setPinned,
    movePinned
  }
})
