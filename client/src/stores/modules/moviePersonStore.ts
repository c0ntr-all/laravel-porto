import { defineStore } from 'pinia'
import { ref } from 'vue'
import { moviePersonApi } from 'src/api/requests/moviePersonApi'
import { mapMoviePersonResponse } from 'src/api/mappers/Movie/movie.mapper'
import { handleApiError } from 'src/utils/jsonapi'
import { withFolderMembership } from 'src/utils/movieFolders'
import { IMovieFolder, IMoviePerson } from 'src/types/Movie'

export const useMoviePersonStore = defineStore('moviePerson', () => {
  const person = ref<IMoviePerson | null>(null)
  const isPersonLoading = ref(false)

  async function getPerson(id: string): Promise<IMoviePerson | null> {
    if (person.value?.id !== id) {
      person.value = null
    }

    isPersonLoading.value = true

    try {
      const response = await moviePersonApi.getPerson(id)
      person.value = mapMoviePersonResponse(response)

      return person.value
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isPersonLoading.value = false
    }
  }

  function setMovieFolderMembership(movieId: string, folder: IMovieFolder, present: boolean): void {
    const current = person.value

    if (!current) {
      return
    }

    person.value = {
      ...current,
      movies: current.movies.map(item => (
        item.id === movieId ? withFolderMembership(item, folder, present) : item
      )),
      credits: current.credits.map(credit => (
        credit.movie.id === movieId
          ? { ...credit, movie: withFolderMembership(credit.movie, folder, present) }
          : credit
      ))
    }
  }

  return {
    person,
    isPersonLoading,
    getPerson,
    setMovieFolderMembership
  }
})
