import { defineStore } from 'pinia'
import { ref } from 'vue'
import { presetApi } from 'src/api/requests/presetApi'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import {
  IJsonApiResponse,
  IFilter,
  IPreset,
  IPresetModel
} from 'src/types'
import { mapPresetFormModelToCreateDto } from 'src/api/mappers/LifeLog/preset.mapper'

export const usePresetStore = defineStore('preset', () => {
  const presets = ref<IPreset[]>([])
  const presetsCount = ref<number>(0)
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const startPresetPostId = ref<string | null>(null)
  const endPresetPostId = ref<string | null>(null)

  async function getPresets(filters: IFilter = {}) {
    isLoading.value = true
    error.value = null
    try {
      const response = await presetApi.getPresets(filters)
      presets.value = mapResponse(response) as IPreset[]
      presetsCount.value = response.meta?.count || 0
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка загрузки'
    } finally {
      isLoading.value = false
    }
  }

  async function createPreset(presetModel: IPresetModel): Promise<IPreset> {
    const presetCreateDto = mapPresetFormModelToCreateDto(presetModel)

    console.log(presetCreateDto)

    try {
      const responseData: IJsonApiResponse = await presetApi.createPreset(presetCreateDto)
      const mappedResponse: IPreset[] = mapResponse(responseData) as IPreset[]
      const newPreset: IPreset = mappedResponse[0]

      presets.value.unshift(newPreset)
      presetsCount.value += 1

      handleApiSuccess(responseData)

      return newPreset
    } catch (error: any) {
      handleApiError(error.message || 'Не удалось создать preset')
      throw error
    }
  }

  function setStartPresetPostId(id: string|null) {
    startPresetPostId.value = id
  }

  function setEndPresetPostId(id: string|null) {
    endPresetPostId.value = id
  }

  return {
    presets,
    presetsCount,
    isLoading,
    startPresetPostId,
    endPresetPostId,
    getPresets,
    createPreset,
    setStartPresetPostId,
    setEndPresetPostId
  }
})
