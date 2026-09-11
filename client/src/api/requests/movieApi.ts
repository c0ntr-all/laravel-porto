import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IMovieListQuery } from 'src/types/Movie'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const movieApi = {
  async getMovies(query?: IMovieListQuery): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      title: query?.title,
      year: query?.year,
      type: query?.type,
      genre_id: query?.genre_id,
      country_id: query?.country_id
    })
    const response = await api.get(
      filters ? `v1/movie/movies?${filters}` : 'v1/movie/movies',
      {
        params: {
          include: 'genres,countries',
          ...(query?.sort ? { sort: query.sort } : {}),
          ...(query?.cursor ? { cursor: query.cursor } : {})
        }
      }
    )

    return response.data
  },

  async getMovie(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/movie/movies/${id}`, {
      params: {
        include: 'genres,countries'
      }
    })

    return response.data
  }
}
