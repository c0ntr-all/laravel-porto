import { defineStore } from 'pinia'
import { ref } from 'vue'
import { movieGenreApi } from 'src/api/requests/movieGenreApi'
import { mapMovieGenreResponse, mapMovieGenresResponse } from 'src/api/mappers/Movie/movie.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IMovieGenre, IMovieGenreWriteDto } from 'src/types/Movie'

export const useMovieGenreStore = defineStore('movieGenres', () => {
  const genres = ref<IMovieGenre[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)

  function upsertGenre(incoming: IMovieGenre): void {
    const index = genres.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      genres.value = [...genres.value, incoming].sort((a, b) => a.name.localeCompare(b.name))
      return
    }

    const next = [...genres.value]
    next.splice(index, 1, incoming)
    genres.value = next.sort((a, b) => a.name.localeCompare(b.name))
  }

  async function getGenres(): Promise<void> {
    isLoading.value = true

    try {
      const response = await movieGenreApi.getGenres()
      genres.value = mapMovieGenresResponse(response)
    } catch (error) {
      handleApiError(error)
    } finally {
      isLoading.value = false
    }
  }

  async function createGenre(payload: IMovieGenreWriteDto): Promise<IMovieGenre | null> {
    isSaving.value = true

    try {
      const response = await movieGenreApi.createGenre(payload)
      const created = mapMovieGenreResponse(response)

      upsertGenre(created)
      handleApiSuccess(response)

      return created
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateGenre(id: string, payload: IMovieGenreWriteDto): Promise<IMovieGenre | null> {
    isSaving.value = true

    try {
      const response = await movieGenreApi.updateGenre(id, payload)
      const updated = mapMovieGenreResponse(response)

      upsertGenre(updated)
      handleApiSuccess(response)

      return updated
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function deleteGenre(id: string): Promise<boolean> {
    isSaving.value = true

    try {
      const response = await movieGenreApi.deleteGenre(id)
      genres.value = genres.value.filter(item => item.id !== id)
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
    genres,
    isLoading,
    isSaving,
    getGenres,
    createGenre,
    updateGenre,
    deleteGenre
  }
})
