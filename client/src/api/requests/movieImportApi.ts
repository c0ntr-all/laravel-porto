import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IMovieImportListQuery } from 'src/types/Movie'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const movieImportApi = {
  async importMovie(kpId: number): Promise<IJsonApiResponse> {
    const response = await api.post(
      'v1/movie/imports',
      { kp_id: kpId },
      { timeout: 45000 }
    )

    return response.data
  },

  async getImports(query?: IMovieImportListQuery): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      kp_id: query?.kp_id,
      status: query?.status
    })
    const response = await api.get(
      filters ? `v1/movie/imports?${filters}` : 'v1/movie/imports',
      {
        params: {
          include: 'movie',
          ...(query?.cursor ? { cursor: query.cursor } : {})
        }
      }
    )

    return response.data
  },

  async getImport(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/movie/imports/${id}`, {
      params: {
        include: 'movie,movie.genres,movie.countries'
      }
    })

    return response.data
  }
}
