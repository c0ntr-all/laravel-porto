import { defineStore } from 'pinia'
import { ref } from 'vue'
import { movieFranchiseApi } from 'src/api/requests/movieFranchiseApi'
import {
  mapMovieFranchiseMoviesResponse,
  mapMovieFranchiseResponse,
  mapMovieFranchisesResponse
} from 'src/api/mappers/Movie/movie.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import {
  IMovieFranchise,
  IMovieFranchiseMovie,
  IMovieFranchiseWriteDto
} from 'src/types/Movie'

export const useMovieFranchiseStore = defineStore('movieFranchises', () => {
  const franchises = ref<IMovieFranchise[]>([])
  const franchise = ref<IMovieFranchise | null>(null)
  const franchiseMovies = ref<IMovieFranchiseMovie[]>([])
  const franchiseMoviesId = ref<string | null>(null)
  const isLoading = ref(false)
  const isSaving = ref(false)
  const isMoviesLoading = ref(false)
  const isMoviesSaving = ref(false)

  function upsertFranchise(incoming: IMovieFranchise): void {
    const index = franchises.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      franchises.value = [...franchises.value, incoming]
        .sort((left, right) => left.order - right.order)
      return
    }

    const next = [...franchises.value]
    next.splice(index, 1, incoming)
    franchises.value = next.sort((left, right) => left.order - right.order)
  }

  async function getFranchises(): Promise<void> {
    isLoading.value = true

    try {
      const response = await movieFranchiseApi.getFranchises()
      franchises.value = mapMovieFranchisesResponse(response)
    } catch (error) {
      handleApiError(error)
    } finally {
      isLoading.value = false
    }
  }

  async function getFranchise(id: string): Promise<IMovieFranchise | null> {
    const cached = franchises.value.find(item => item.id === id)

    if (cached) {
      franchise.value = cached
    }

    isLoading.value = true

    try {
      const response = await movieFranchiseApi.getFranchise(id)
      franchise.value = mapMovieFranchiseResponse(response)
      upsertFranchise(franchise.value)

      return franchise.value
    } catch (error) {
      handleApiError(error)
      return cached ?? null
    } finally {
      isLoading.value = false
    }
  }

  async function createFranchise(payload: IMovieFranchiseWriteDto): Promise<IMovieFranchise | null> {
    isSaving.value = true

    try {
      const response = await movieFranchiseApi.createFranchise(payload)
      const created = mapMovieFranchiseResponse(response)

      upsertFranchise(created)
      handleApiSuccess(response)

      return created
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateFranchise(
    id: string,
    payload: IMovieFranchiseWriteDto
  ): Promise<IMovieFranchise | null> {
    isSaving.value = true

    try {
      const response = await movieFranchiseApi.updateFranchise(id, payload)
      const updated = mapMovieFranchiseResponse(response)

      upsertFranchise(updated)

      if (franchise.value?.id === id) {
        franchise.value = updated
      }

      handleApiSuccess(response)

      return updated
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function deleteFranchise(id: string): Promise<boolean> {
    isSaving.value = true

    try {
      const response = await movieFranchiseApi.deleteFranchise(id)

      franchises.value = franchises.value.filter(item => item.id !== id)

      if (franchise.value?.id === id) {
        franchise.value = null
        franchiseMovies.value = []
        franchiseMoviesId.value = null
      }

      handleApiSuccess(response)

      return true
    } catch (error) {
      handleApiError(error)
      return false
    } finally {
      isSaving.value = false
    }
  }

  async function getFranchiseMovies(franchiseId: string): Promise<IMovieFranchiseMovie[]> {
    franchiseMoviesId.value = franchiseId
    isMoviesLoading.value = true

    try {
      const response = await movieFranchiseApi.getFranchiseMovies(franchiseId, { per_page: 100 })
      franchiseMovies.value = mapMovieFranchiseMoviesResponse(response)

      return franchiseMovies.value
    } catch (error) {
      handleApiError(error)
      franchiseMovies.value = []

      return []
    } finally {
      isMoviesLoading.value = false
    }
  }

  async function attachMovie(
    franchiseId: string,
    movieId: string
  ): Promise<boolean> {
    isMoviesSaving.value = true

    try {
      const response = await movieFranchiseApi.attachMovie(franchiseId, movieId)
      await getFranchiseMovies(franchiseId)
      handleApiSuccess(response)

      return true
    } catch (error) {
      handleApiError(error)
      return false
    } finally {
      isMoviesSaving.value = false
    }
  }

  async function detachMovie(franchiseId: string, movieId: string): Promise<boolean> {
    isMoviesSaving.value = true

    try {
      const response = await movieFranchiseApi.detachMovie(franchiseId, movieId)
      franchiseMovies.value = franchiseMovies.value.filter(item => item.id !== movieId)
      handleApiSuccess(response)

      return true
    } catch (error) {
      handleApiError(error)
      return false
    } finally {
      isMoviesSaving.value = false
    }
  }

  async function reorderFranchiseMovies(
    franchiseId: string,
    orderedMovieIds: string[]
  ): Promise<void> {
    const previous = [...franchiseMovies.value]
    const byId = new Map(franchiseMovies.value.map(item => [item.id, item]))

    franchiseMovies.value = orderedMovieIds
      .map((id, index) => {
        const movie = byId.get(id)

        if (!movie) {
          return null
        }

        return {
          ...movie,
          order: index + 1
        }
      })
      .filter((item): item is IMovieFranchiseMovie => Boolean(item))

    isMoviesSaving.value = true

    try {
      await Promise.all(
        franchiseMovies.value.map((movie, index) => (
          movieFranchiseApi.updateMovieOrder(franchiseId, movie.id, index + 1)
        ))
      )
    } catch (error) {
      franchiseMovies.value = previous
      handleApiError(error)
    } finally {
      isMoviesSaving.value = false
    }
  }

  return {
    franchises,
    franchise,
    franchiseMovies,
    franchiseMoviesId,
    isLoading,
    isSaving,
    isMoviesLoading,
    isMoviesSaving,
    getFranchises,
    getFranchise,
    createFranchise,
    updateFranchise,
    deleteFranchise,
    getFranchiseMovies,
    attachMovie,
    detachMovie,
    reorderFranchiseMovies
  }
})
