import { defineStore } from 'pinia'
import { ref } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMovieResponse, mapMoviesResponse } from 'src/api/mappers/Movie/movie.mapper'
import { extractCursorFromResponse, handleApiError, handleApiSuccess, hasMoreFromResponse } from 'src/utils/jsonapi'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { IMovie, IMovieWriteDto } from 'src/types/Movie'
import { useMovieStore } from 'src/stores/modules/movieStore'

function mergeById(current: IMovie[], incoming: IMovie[]): IMovie[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

export const useMovieAdminStore = defineStore('movieAdmin', () => {
  const movies = ref<IMovie[]>([])
  const moviesCursor = ref<string | null>(null)
  const hasMoreMovies = ref(false)
  const isMoviesLoading = ref(false)
  const isMoviesLoadingMore = ref(false)
  const isSaving = ref(false)
  const listTitle = ref('')
  const listType = ref<MovieTypeEnum | null>(null)

  let listRequestId = 0

  function syncPublicStore(incoming: IMovie): void {
    const publicStore = useMovieStore()
    const index = publicStore.movies.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      if (publicStore.movies.length) {
        publicStore.movies = [incoming, ...publicStore.movies]
      }

      return
    }

    publicStore.movies.splice(index, 1, incoming)

    if (publicStore.movie?.id === incoming.id) {
      publicStore.movie = incoming
    }
  }

  function removeFromPublicStore(id: string): void {
    const publicStore = useMovieStore()
    publicStore.movies = publicStore.movies.filter(item => item.id !== id)

    if (publicStore.movie?.id === id) {
      publicStore.movie = null
    }
  }

  function upsertMovie(incoming: IMovie): void {
    const index = movies.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      movies.value = [incoming, ...movies.value]
      return
    }

    movies.value.splice(index, 1, incoming)
  }

  async function getMovies(options?: {
    append?: boolean
    title?: string
    type?: MovieTypeEnum | null
  }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'title' in options) {
      listTitle.value = options.title?.trim() ?? ''
    }

    if (options && 'type' in options) {
      listType.value = options.type ?? null
    }

    if (append) {
      if (!moviesCursor.value || isMoviesLoadingMore.value || isMoviesLoading.value) {
        return
      }

      isMoviesLoadingMore.value = true
    } else {
      listRequestId += 1
      isMoviesLoading.value = true
      isMoviesLoadingMore.value = false
      movies.value = []
      moviesCursor.value = null
      hasMoreMovies.value = false
    }

    const requestId = listRequestId

    try {
      const response = await movieApi.getMovies({
        title: listTitle.value || undefined,
        type: listType.value ?? undefined,
        cursor: append ? moviesCursor.value : null
      })

      if (requestId !== listRequestId) {
        return
      }

      const mapped = mapMoviesResponse(response)
      movies.value = append ? mergeById(movies.value, mapped) : mapped
      moviesCursor.value = extractCursorFromResponse(response)
      hasMoreMovies.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId !== listRequestId) {
        return
      }

      handleApiError(error)
    } finally {
      if (requestId === listRequestId) {
        isMoviesLoading.value = false
        isMoviesLoadingMore.value = false
      }
    }
  }

  async function createMovie(payload: IMovieWriteDto): Promise<IMovie | null> {
    isSaving.value = true

    try {
      const response = await movieApi.createMovie(payload)
      const created = mapMovieResponse(response)

      upsertMovie(created)
      syncPublicStore(created)
      handleApiSuccess(response)

      return created
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateMovie(id: string, payload: IMovieWriteDto): Promise<IMovie | null> {
    isSaving.value = true

    try {
      const response = await movieApi.updateMovie(id, payload)
      const updated = mapMovieResponse(response)

      upsertMovie(updated)
      syncPublicStore(updated)
      handleApiSuccess(response)

      return updated
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function deleteMovie(id: string): Promise<boolean> {
    isSaving.value = true

    try {
      const response = await movieApi.deleteMovie(id)
      movies.value = movies.value.filter(item => item.id !== id)
      removeFromPublicStore(id)
      handleApiSuccess(response)

      return true
    } catch (error) {
      handleApiError(error)
      return false
    } finally {
      isSaving.value = false
    }
  }

  return {
    movies,
    moviesCursor,
    hasMoreMovies,
    isMoviesLoading,
    isMoviesLoadingMore,
    isSaving,
    listTitle,
    listType,
    getMovies,
    createMovie,
    updateMovie,
    deleteMovie
  }
})
