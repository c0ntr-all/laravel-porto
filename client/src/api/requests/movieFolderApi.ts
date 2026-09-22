import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const movieFolderApi = {
  async getFolders(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/movie/folders')

    return response.data
  },

  async getFolder(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/movie/folders/${id}`)

    return response.data
  },

  async getFolderMovies(
    folderId: string,
    query?: { page?: number, per_page?: number, sort?: string }
  ): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/movie/folders/${folderId}/movies`, {
      params: {
        include: 'genres,countries',
        per_page: query?.per_page ?? 24,
        ...(query?.page ? { page: query.page } : {}),
        ...(query?.sort ? { sort: query.sort } : {})
      }
    })

    return response.data
  },

  async attachMovie(folderId: string, movieId: string): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/movie/folders/${folderId}/movies`, {
      movie_id: Number(movieId)
    })

    return response.data
  },

  async detachMovie(folderId: string, movieId: string): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/movie/folders/${folderId}/movies/${movieId}`)

    return response.data
  }
}
