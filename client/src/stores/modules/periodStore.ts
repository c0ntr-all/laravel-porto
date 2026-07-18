import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import { periodApi } from 'src/api/requests/periodApi'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import {
  IJsonApiResponse,
  IPost,
  IFilter,
  IPeriod,
  IPeriodModel, IPeriodCreateDto
} from 'src/types'
import { mapPeriodFormModelToCreateDto } from 'src/api/mappers/LifeLog/period.mapper'

export const usePeriodStore = defineStore('period', () => {
  const periods = ref<IPost[]>([])
  const periodsCount = ref<number>(0)
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const startPeriodPostId = ref<string | null>(null)
  const endPeriodPostId = ref<string | null>(null)

  async function getPeriods(filters: IFilter = {}) {
    isLoading.value = true
    error.value = null
    try {
      const response = await periodApi.getPeriods(filters)
      periods.value = mapResponse(response) as IPeriod[]
      periodsCount.value = response.meta?.count || 0
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка загрузки'
    } finally {
      isLoading.value = false
    }
  }

  async function createPeriod(periodModel: IPeriodModel): Promise<IPeriod> {
    const periodCreateDto: IPeriodCreateDto = mapPeriodFormModelToCreateDto(periodModel)

    try {
      const responseData: IJsonApiResponse = await periodApi.createPeriod(periodCreateDto)
      const mappedResponse: IPeriod[] = mapResponse(responseData) as IPeriod[]
      const newPeriod: IPeriod = mappedResponse[0]

      periods.value.unshift(newPeriod)
      periodsCount.value += 1

      handleApiSuccess(responseData)

      return newPeriod
    } catch (error: any) {
      handleApiError(error.message || 'Не удалось создать период')
      throw error
    }
  }

  function setStartPeriodPostId(id: string|null) {
    startPeriodPostId.value = id
  }

  function setEndPeriodPostId(id: string|null) {
    endPeriodPostId.value = id
  }

  return {
    periods,
    periodsCount,
    isLoading,
    startPeriodPostId,
    endPeriodPostId,
    getPeriods,
    createPeriod,
    setStartPeriodPostId,
    setEndPeriodPostId
  }
})
