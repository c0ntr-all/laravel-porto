import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IMovieGenreWriteDto } from 'src/types/Movie'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const movieGenreApi = {
  async getGenres(query?: { name?: string }): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      name: query?.name
    })
    const response = await api.get(
      filters ? `v1/movie/genres?${filters}` : 'v1/movie/genres'
    )

    return response.data
  },

  async createGenre(payload: IMovieGenreWriteDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/movie/genres', payload)

    return response.data
  },

  async updateGenre(id: string, payload: IMovieGenreWriteDto): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/movie/genres/${id}`, payload)

    return response.data
  },

  async deleteGenre(id: string): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/movie/genres/${id}`)

    return response.data
  }
}
