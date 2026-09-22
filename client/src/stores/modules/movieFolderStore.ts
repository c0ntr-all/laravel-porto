import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { Notify } from 'quasar'
import { movieFolderApi } from 'src/api/requests/movieFolderApi'
import {
  mapMovieFolderResponse,
  mapMovieFoldersResponse,
  mapMoviesResponse
} from 'src/api/mappers/Movie/movie.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { SystemMovieFolderEnum } from 'src/enums/Movie/SystemMovieFolderEnum'
import { IMovie, IMovieFolder } from 'src/types/Movie'
import { useMovieStore } from 'src/stores/modules/movieStore'
import {
  isMovieInFolderRecord,
  isValidMovieFolderName,
  sortedMovieFolders,
  withFolderMembership
} from 'src/utils/movieFolders'

function mergeById(current: IMovie[], incoming: IMovie[]): IMovie[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

export const useMovieFolderStore = defineStore('movieFolders', () => {
  const folders = ref<IMovieFolder[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const pendingKeys = ref<string[]>([])
  const folderMovies = ref<IMovie[]>([])
  const folderMoviesId = ref<string | null>(null)
  const folderMoviesPage = ref(1)
  const folderMoviesLastPage = ref(1)
  const isFolderMoviesLoading = ref(false)
  const isFolderMoviesLoadingMore = ref(false)
  let foldersRequest: Promise<void> | null = null
  let folderMoviesRequestId = 0

  const hasMoreFolderMovies = computed(() => (
    folderMoviesPage.value < folderMoviesLastPage.value
  ))
  const sortedFolders = computed(() => sortedMovieFolders(folders.value))

  function upsertFolder(incoming: IMovieFolder): void {
    const index = folders.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      folders.value = [...folders.value, incoming]
      return
    }

    const next = [...folders.value]
    next.splice(index, 1, incoming)
    folders.value = next
  }

  function folderById(id: string): IMovieFolder | null {
    return folders.value.find(folder => String(folder.id) === String(id)) ?? null
  }

  function folderBySlug(slug: SystemMovieFolderEnum): IMovieFolder | null {
    return folders.value.find(folder => folder.slug === slug) ?? null
  }

  function isPending(movieId: string, folderId: string): boolean {
    return pendingKeys.value.includes(`${movieId}:${folderId}`)
  }

  function isMovieInFolder(movie: IMovie, slug: SystemMovieFolderEnum): boolean {
    const folder = folderBySlug(slug)

    if (folder) {
      return isMovieInFolderRecord(movie, folder)
    }

    return (movie.folder_slugs ?? []).includes(slug)
  }

  function patchMovieMembership(movie: IMovie, folder: IMovieFolder, present: boolean): void {
    const next = withFolderMembership(movie, folder, present)
    useMovieStore().setMovieFolderMembership(movie.id, folder, present)
    folderMovies.value = folderMovies.value.map(item => (
      item.id === movie.id ? next : item
    ))
    syncOpenFolderMovies(next, folder.id, present)
  }

  function syncOpenFolderMovies(movie: IMovie, folderId: string, present: boolean): void {
    if (folderMoviesId.value !== folderId) {
      return
    }

    if (!present) {
      folderMovies.value = folderMovies.value.filter(item => item.id !== movie.id)
      return
    }

    if (folderMovies.value.some(item => item.id === movie.id)) {
      return
    }

    folderMovies.value = [movie, ...folderMovies.value]
  }

  async function getFolders(): Promise<void> {
    if (folders.value.length) {
      return
    }

    if (foldersRequest) {
      await foldersRequest
      return
    }

    isLoading.value = true
    foldersRequest = (async () => {
      try {
        const response = await movieFolderApi.getFolders()
        folders.value = mapMovieFoldersResponse(response)
      } catch (error) {
        handleApiError(error)
      }
    })()

    try {
      await foldersRequest
    } finally {
      foldersRequest = null
      isLoading.value = false
    }
  }

  async function getFolder(id: string): Promise<IMovieFolder | null> {
    const listed = folderById(id)

    if (listed) {
      return listed
    }

    await getFolders()

    const fromList = folderById(id)

    if (fromList) {
      return fromList
    }

    try {
      const response = await movieFolderApi.getFolder(id)
      const mapped = mapMovieFolderResponse(response)
      upsertFolder(mapped)

      return mapped
    } catch (error) {
      handleApiError(error)
      return null
    }
  }

  async function getFolderMovies(folderId: string, options?: { append?: boolean }): Promise<void> {
    const append = Boolean(options?.append)

    if (append) {
      if (
        folderMoviesId.value !== folderId ||
        !hasMoreFolderMovies.value ||
        isFolderMoviesLoading.value ||
        isFolderMoviesLoadingMore.value
      ) {
        return
      }

      isFolderMoviesLoadingMore.value = true
      folderMoviesPage.value += 1
    } else {
      folderMoviesRequestId += 1
      folderMoviesId.value = folderId
      folderMovies.value = []
      folderMoviesPage.value = 1
      folderMoviesLastPage.value = 1
      isFolderMoviesLoading.value = true
      isFolderMoviesLoadingMore.value = false
    }

    const requestId = folderMoviesRequestId
    const page = folderMoviesPage.value

    try {
      const response = await movieFolderApi.getFolderMovies(folderId, {
        page,
        sort: '-added_at'
      })

      if (requestId !== folderMoviesRequestId || folderMoviesId.value !== folderId) {
        return
      }

      const mapped = mapMoviesResponse(response)
      folderMovies.value = append ? mergeById(folderMovies.value, mapped) : mapped
      folderMoviesLastPage.value = Number(response.meta?.last_page ?? 1)
    } catch (error) {
      if (requestId !== folderMoviesRequestId) {
        return
      }

      if (append) {
        folderMoviesPage.value = Math.max(1, folderMoviesPage.value - 1)
      }

      handleApiError(error)
    } finally {
      if (requestId === folderMoviesRequestId) {
        isFolderMoviesLoading.value = false
        isFolderMoviesLoadingMore.value = false
      }
    }
  }

  async function createFolder(name: string): Promise<IMovieFolder | null> {
    const trimmed = name.trim()

    if (!isValidMovieFolderName(trimmed)) {
      Notify.create({
        type: 'negative',
        message: 'Название: до 30 букв и цифр'
      })
      return null
    }

    isSaving.value = true

    try {
      const response = await movieFolderApi.createFolder(trimmed)
      const mapped = mapMovieFolderResponse(response)
      upsertFolder(mapped)
      handleApiSuccess(response)

      return mapped
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function toggleMovieInFolder(movie: IMovie, folder: IMovieFolder): Promise<void> {
    const key = `${movie.id}:${folder.id}`

    if (pendingKeys.value.includes(key)) {
      return
    }

    const attached = isMovieInFolderRecord(movie, folder)
    pendingKeys.value = [...pendingKeys.value, key]
    patchMovieMembership(movie, folder, !attached)

    try {
      const response = attached
        ? await movieFolderApi.detachMovie(folder.id, movie.id)
        : await movieFolderApi.attachMovie(folder.id, movie.id)

      upsertFolder(mapMovieFolderResponse(response))
      handleApiSuccess(response)
    } catch (error) {
      patchMovieMembership(movie, folder, attached)
      handleApiError(error)
    } finally {
      pendingKeys.value = pendingKeys.value.filter(item => item !== key)
    }
  }

  async function toggleMovieFolder(movie: IMovie, slug: SystemMovieFolderEnum): Promise<void> {
    if (!folders.value.length) {
      await getFolders()
    }

    const folder = folderBySlug(slug)

    if (!folder) {
      Notify.create({
        type: 'negative',
        message: 'Папка не найдена'
      })
      return
    }

    await toggleMovieInFolder(movie, folder)
  }

  return {
    folders,
    sortedFolders,
    isLoading,
    isSaving,
    folderMovies,
    folderMoviesId,
    isFolderMoviesLoading,
    isFolderMoviesLoadingMore,
    hasMoreFolderMovies,
    getFolders,
    getFolder,
    getFolderMovies,
    createFolder,
    folderById,
    folderBySlug,
    isPending,
    isMovieInFolder,
    toggleMovieFolder,
    toggleMovieInFolder
  }
})
