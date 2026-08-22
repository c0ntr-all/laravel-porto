import { defineStore } from 'pinia'
import { ref } from 'vue'
import { presetApi } from 'src/api/requests/presetApi'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import {
  IJsonApiResponse,
  IFilter,
  IPreset,
  IPresetModel
} from 'src/types'
import { mapPresetFormModelToCreateDto, mapPresetResponse, mapPresetsResponse } from 'src/api/mappers/LifeLog/preset.mapper'

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
      presets.value = mapPresetsResponse(response)
      presetsCount.value = response.meta?.count || 0
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка загрузки'
    } finally {
      isLoading.value = false
    }
  }

  async function getPreset(id: string): Promise<IPreset> {
    const response = await presetApi.getPreset(id)
    return mapPresetResponse(response)
  }

  async function createPreset(presetModel: IPresetModel): Promise<IPreset> {
    const presetCreateDto = mapPresetFormModelToCreateDto(presetModel)

    try {
      const responseData: IJsonApiResponse = await presetApi.createPreset(presetCreateDto)
      const newPreset = mapPresetResponse(responseData)

      presets.value.unshift(newPreset)
      presetsCount.value += 1

      handleApiSuccess(responseData)

      return newPreset
    } catch (error: any) {
      handleApiError(error.message || 'Не удалось создать preset')
      throw error
    }
  }

  async function updatePreset(id: string, presetModel: IPresetModel): Promise<IPreset> {
    const presetUpdateDto = mapPresetFormModelToCreateDto(presetModel)

    try {
      const responseData: IJsonApiResponse = await presetApi.updatePreset(id, presetUpdateDto)
      const updatedPreset = mapPresetResponse(responseData)
      const index = presets.value.findIndex(preset => preset.id === id)

      if (index !== -1) {
        presets.value.splice(index, 1, updatedPreset)
      }

      handleApiSuccess(responseData)

      return updatedPreset
    } catch (error: any) {
      handleApiError(error.message || 'Не удалось обновить preset')
      throw error
    }
  }

  async function deletePreset(id: string): Promise<void> {
    try {
      const responseData: IJsonApiResponse = await presetApi.deletePreset(id)

      presets.value = presets.value.filter(preset => preset.id !== id)
      presetsCount.value = Math.max(0, presetsCount.value - 1)

      handleApiSuccess(responseData)
    } catch (error: any) {
      handleApiError(error.message || 'Не удалось удалить preset')
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
    getPreset,
    createPreset,
    updatePreset,
    deletePreset,
    setStartPresetPostId,
    setEndPresetPostId
  }
})
