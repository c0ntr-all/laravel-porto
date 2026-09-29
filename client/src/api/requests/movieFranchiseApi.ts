import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IMovieFranchiseWriteDto } from 'src/types/Movie'

function toFranchiseFormData(
  payload: IMovieFranchiseWriteDto,
  method?: 'PATCH'
): FormData {
  const formData = new FormData()

  if (method === 'PATCH') {
    formData.append('_method', 'PATCH')
  }

  if (payload.name !== undefined) {
    formData.append('name', payload.name)
  }

  if (payload.description !== undefined) {
    formData.append('description', payload.description ?? '')
  }

  if (payload.order !== undefined) {
    formData.append('order', String(payload.order))
  }

  if (payload.image_file) {
    formData.append('image_file', payload.image_file)
  }

  return formData
}

function hasImageFile(payload: IMovieFranchiseWriteDto): boolean {
  return Boolean(payload.image_file)
}

export const movieFranchiseApi = {
  async getFranchises(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/movie/franchises')

    return response.data
  },

  async getFranchise(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/movie/franchises/${id}`)

    return response.data
  },

  async createFranchise(payload: IMovieFranchiseWriteDto): Promise<IJsonApiResponse> {
    const response = hasImageFile(payload)
      ? await api.post('v1/movie/franchises', toFranchiseFormData(payload))
      : await api.post('v1/movie/franchises', {
        name: payload.name,
        description: payload.description,
        order: payload.order
      })

    return response.data
  },

  async updateFranchise(id: string, payload: IMovieFranchiseWriteDto): Promise<IJsonApiResponse> {
    const response = hasImageFile(payload)
      ? await api.post(`v1/movie/franchises/${id}`, toFranchiseFormData(payload, 'PATCH'))
      : await api.patch(`v1/movie/franchises/${id}`, {
        name: payload.name,
        description: payload.description,
        order: payload.order
      })

    return response.data
  },

  async deleteFranchise(id: string): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/movie/franchises/${id}`)

    return response.data
  },

  async getFranchiseMovies(
    franchiseId: string,
    query?: { page?: number, per_page?: number }
  ): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/movie/franchises/${franchiseId}/movies`, {
      params: {
        include: 'genres,countries',
        per_page: query?.per_page ?? 100,
        ...(query?.page ? { page: query.page } : {})
      }
    })

    return response.data
  },

  async attachMovie(
    franchiseId: string,
    movieId: string,
    order?: number
  ): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/movie/franchises/${franchiseId}/movies`, {
      movie_id: Number(movieId),
      ...(order !== undefined ? { order } : {})
    })

    return response.data
  },

  async updateMovieOrder(
    franchiseId: string,
    movieId: string,
    order: number
  ): Promise<IJsonApiResponse> {
    const response = await api.patch(
      `v1/movie/franchises/${franchiseId}/movies/${movieId}`,
      { order }
    )

    return response.data
  },

  async detachMovie(franchiseId: string, movieId: string): Promise<IJsonApiResponse> {
    const response = await api.delete(
      `v1/movie/franchises/${franchiseId}/movies/${movieId}`
    )

    return response.data
  }
}
