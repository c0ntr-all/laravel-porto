import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const moviePersonApi = {
  async getPerson(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/movie/persons/${id}`, {
      params: {
        include: 'profession,professions,movies'
      }
    })

    return response.data
  }
}
