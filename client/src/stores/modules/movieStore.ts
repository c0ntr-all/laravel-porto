import { defineStore } from 'pinia'
import { ref } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import { mapMovieCreditsResponse, mapMovieResponse, mapMoviesResponse } from 'src/api/mappers/Movie/movie.mapper'
import { extractCursorFromResponse, handleApiError, hasMoreFromResponse } from 'src/utils/jsonapi'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { IMovie, IMovieCredit } from 'src/types/Movie'

function mergeById(current: IMovie[], incoming: IMovie[]): IMovie[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

export const useMovieStore = defineStore('movies', () => {
  const movies = ref<IMovie[]>([])
  const movie = ref<IMovie | null>(null)
  const movieCredits = ref<IMovieCredit[]>([])
  const moviesCursor = ref<string | null>(null)
  const hasMoreMovies = ref(false)
  const isMoviesLoading = ref(false)
  const isMoviesLoadingMore = ref(false)
  const isMovieLoading = ref(false)
  const isMovieCreditsLoading = ref(false)
  const listTitle = ref('')
  const listType = ref<MovieTypeEnum | null>(null)

  let listRequestId = 0
  let creditsMovieId: string | null = null

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

  async function getMovie(id: string): Promise<IMovie | null> {
    const listed = movies.value.find(item => item.id === id)

    if (movie.value?.id !== id) {
      movie.value = listed ?? null
    }

    isMovieLoading.value = true

    try {
      const response = await movieApi.getMovie(id)
      movie.value = mapMovieResponse(response)

      return movie.value
    } catch (error) {
      handleApiError(error)
      return listed ?? null
    } finally {
      isMovieLoading.value = false
    }
  }

  async function getMovieCredits(id: string): Promise<IMovieCredit[]> {
    if (creditsMovieId !== id) {
      movieCredits.value = []
      creditsMovieId = id
    }

    isMovieCreditsLoading.value = true

    try {
      const response = await movieApi.getMovieCredits(id)
      movieCredits.value = mapMovieCreditsResponse(response)

      return movieCredits.value
    } catch (error) {
      handleApiError(error)
      return []
    } finally {
      isMovieCreditsLoading.value = false
    }
  }

  return {
    movies,
    movie,
    movieCredits,
    moviesCursor,
    hasMoreMovies,
    isMoviesLoading,
    isMoviesLoadingMore,
    isMovieLoading,
    isMovieCreditsLoading,
    listTitle,
    listType,
    getMovies,
    getMovie,
    getMovieCredits
  }
})
