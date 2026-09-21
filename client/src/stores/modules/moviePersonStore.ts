import { defineStore } from 'pinia'
import { ref } from 'vue'
import { moviePersonApi } from 'src/api/requests/moviePersonApi'
import { mapMoviePersonResponse } from 'src/api/mappers/Movie/movie.mapper'
import { handleApiError } from 'src/utils/jsonapi'
import { IMoviePerson } from 'src/types/Movie'

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

  return {
    person,
    isPersonLoading,
    getPerson
  }
})
