import { defineStore } from 'pinia'
import { ref } from 'vue'
import { Notify } from 'quasar'
import { movieFolderApi } from 'src/api/requests/movieFolderApi'
import { mapMovieFolderResponse, mapMovieFoldersResponse } from 'src/api/mappers/Movie/movie.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { SystemMovieFolderEnum } from 'src/enums/Movie/SystemMovieFolderEnum'
import { IMovie, IMovieFolder } from 'src/types/Movie'
import { useMovieStore } from 'src/stores/modules/movieStore'

export const useMovieFolderStore = defineStore('movieFolders', () => {
  const folders = ref<IMovieFolder[]>([])
  const isLoading = ref(false)
  const pendingKeys = ref<string[]>([])
  let foldersRequest: Promise<void> | null = null

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

  function folderBySlug(slug: SystemMovieFolderEnum): IMovieFolder | null {
    return folders.value.find(folder => folder.slug === slug) ?? null
  }

  function isPending(movieId: string, slug: SystemMovieFolderEnum): boolean {
    return pendingKeys.value.includes(`${movieId}:${slug}`)
  }

  function isMovieInFolder(movie: IMovie, slug: SystemMovieFolderEnum): boolean {
    return (movie.folder_slugs ?? []).includes(slug)
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

    const key = `${movie.id}:${slug}`

    if (pendingKeys.value.includes(key)) {
      return
    }

    const attached = isMovieInFolder(movie, slug)
    pendingKeys.value = [...pendingKeys.value, key]
    useMovieStore().setMovieFolderSlug(movie.id, slug, !attached)

    try {
      const response = attached
        ? await movieFolderApi.detachMovie(folder.id, movie.id)
        : await movieFolderApi.attachMovie(folder.id, movie.id)

      upsertFolder(mapMovieFolderResponse(response))
      handleApiSuccess(response)
    } catch (error) {
      useMovieStore().setMovieFolderSlug(movie.id, slug, attached)
      handleApiError(error)
    } finally {
      pendingKeys.value = pendingKeys.value.filter(item => item !== key)
    }
  }

  return {
    folders,
    isLoading,
    getFolders,
    folderBySlug,
    isPending,
    isMovieInFolder,
    toggleMovieFolder
  }
})
