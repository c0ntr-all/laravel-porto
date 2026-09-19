import { defineStore } from 'pinia'
import { ref } from 'vue'
import { AxiosError } from 'axios'
import { Notify } from 'quasar'
import { movieImportApi } from 'src/api/requests/movieImportApi'
import { mapMovieImportResponse, mapMovieImportsResponse } from 'src/api/mappers/Movie/movie.mapper'
import { extractCursorFromResponse, handleApiError, handleApiSuccess, hasMoreFromResponse } from 'src/utils/jsonapi'
import { useMovieAdminStore } from 'src/stores/modules/movieAdminStore'
import { useMovieGenreStore } from 'src/stores/modules/movieGenreStore'
import { MovieImportStatusEnum } from 'src/enums/Movie/MovieImportStatusEnum'
import { IJsonApiResponse } from 'src/types'
import { IMovieImport } from 'src/types/Movie'

function mergeById(current: IMovieImport[], incoming: IMovieImport[]): IMovieImport[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

function importResponseFromError(error: unknown): IJsonApiResponse | null {
  if (!(error instanceof AxiosError) || !error.response?.data) {
    return null
  }

  const data = error.response.data as IJsonApiResponse
  const resource = Array.isArray(data.data) ? data.data[0] : data.data

  if (resource?.type !== 'movie_imports') {
    return null
  }

  return data
}

export const useMovieImportStore = defineStore('movieImports', () => {
  const imports = ref<IMovieImport[]>([])
  const importsCursor = ref<string | null>(null)
  const hasMoreImports = ref(false)
  const isLoading = ref(false)
  const isLoadingMore = ref(false)
  const isImporting = ref(false)
  const lastImport = ref<IMovieImport | null>(null)

  let listRequestId = 0

  function upsertImport(incoming: IMovieImport): void {
    lastImport.value = incoming
    const index = imports.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      imports.value = [incoming, ...imports.value]
      return
    }

    imports.value.splice(index, 1, incoming)
  }

  function applyImportedMovie(imported: IMovieImport): void {
    if (!imported.movie || imported.status !== MovieImportStatusEnum.COMPLETED) {
      return
    }

    useMovieAdminStore().upsertImportedMovie(imported.movie)
    void useMovieGenreStore().getGenres()
  }

  async function getImports(options?: { append?: boolean }): Promise<void> {
    const append = Boolean(options?.append)

    if (append) {
      if (!importsCursor.value || isLoadingMore.value || isLoading.value) {
        return
      }

      isLoadingMore.value = true
    } else {
      listRequestId += 1
      isLoading.value = true
      isLoadingMore.value = false
      imports.value = []
      importsCursor.value = null
      hasMoreImports.value = false
    }

    const requestId = listRequestId

    try {
      const response = await movieImportApi.getImports({
        cursor: append ? importsCursor.value : null
      })

      if (requestId !== listRequestId) {
        return
      }

      const mapped = mapMovieImportsResponse(response)
      imports.value = append ? mergeById(imports.value, mapped) : mapped
      importsCursor.value = extractCursorFromResponse(response)
      hasMoreImports.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId !== listRequestId) {
        return
      }

      handleApiError(error)
    } finally {
      if (requestId === listRequestId) {
        isLoading.value = false
        isLoadingMore.value = false
      }
    }
  }

  async function importFromKinopoisk(kpId: number): Promise<IMovieImport | null> {
    isImporting.value = true

    try {
      const response = await movieImportApi.importMovie(kpId)
      const mapped = mapMovieImportResponse(response)

      upsertImport(mapped)
      applyImportedMovie(mapped)
      handleApiSuccess(response)

      return mapped
    } catch (error) {
      const failedResponse = importResponseFromError(error)

      if (failedResponse) {
        const mapped = mapMovieImportResponse(failedResponse)

        upsertImport(mapped)
        Notify.create({
          type: 'negative',
          message: mapped.error_message || failedResponse.meta?.message || 'Kinopoisk import failed'
        })

        return mapped
      }

      handleApiError(error)
      return null
    } finally {
      isImporting.value = false
    }
  }

  return {
    imports,
    importsCursor,
    hasMoreImports,
    isLoading,
    isLoadingMore,
    isImporting,
    lastImport,
    getImports,
    importFromKinopoisk
  }
})
