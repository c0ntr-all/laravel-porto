import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { musicTagApi } from 'src/api/requests/musicTagApi'
import { musicTagGroupApi } from 'src/api/requests/musicTagGroupApi'
import { mapTagResponse, mapTagsResponse } from 'src/api/mappers/Music/tag.mapper'
import { mapTagGroupResponse, mapTagGroupsResponse } from 'src/api/mappers/Music/tagGroup.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IMusicTag, IMusicTagGroup, IMusicTagGroupWriteDto, IMusicTagWriteDto } from 'src/types'

export const useMusicTagStore = defineStore('musicTag', () => {
  const groups = ref<IMusicTagGroup[]>([])
  const tags = ref<IMusicTag[]>([])
  const isGroupsLoading = ref(false)
  const isTagsLoading = ref(false)
  const isSaving = ref(false)

  const ungroupedTags = computed(() => tags.value.filter(tag => !tag.group_id))

  function tagsByGroup(groupId: string): IMusicTag[] {
    return tags.value.filter(tag => tag.group_id === groupId)
  }

  function flattenTags(nodes: IMusicTag[] = tags.value): IMusicTag[] {
    return nodes.flatMap(tag => [tag, ...flattenTags(tag.tags ?? [])])
  }

  function parentOptions(groupId?: string | null, excludeId?: string): IMusicTag[] {
    const source = groupId
      ? flattenTags(tagsByGroup(groupId))
      : flattenTags()

    return source.filter(tag => tag.id !== excludeId)
  }

  function upsertGroup(group: IMusicTagGroup): void {
    const index = groups.value.findIndex(item => item.id === group.id)
    const next = index === -1
      ? [...groups.value, group]
      : groups.value.map((item, itemIndex) => itemIndex === index ? group : item)

    groups.value = [...next].sort((a, b) => {
      if (a.display_order !== b.display_order) {
        return a.display_order - b.display_order
      }

      return a.name.localeCompare(b.name)
    })
  }

  async function getGroups(): Promise<void> {
    isGroupsLoading.value = true

    try {
      const response = await musicTagGroupApi.getGroups()
      groups.value = mapTagGroupsResponse(response)
    } catch (error) {
      handleApiError(error)
    } finally {
      isGroupsLoading.value = false
    }
  }

  async function getTags(): Promise<void> {
    isTagsLoading.value = true

    try {
      const response = await musicTagApi.getTags()
      tags.value = mapTagsResponse(response)
    } catch (error) {
      handleApiError(error)
    } finally {
      isTagsLoading.value = false
    }
  }

  async function loadAll(): Promise<void> {
    await Promise.all([getGroups(), getTags()])
  }

  async function createGroup(payload: IMusicTagGroupWriteDto): Promise<IMusicTagGroup | null> {
    isSaving.value = true

    try {
      const response = await musicTagGroupApi.createGroup(payload)
      const group = mapTagGroupResponse(response)
      upsertGroup(group)
      handleApiSuccess(response)

      return group
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateGroup(id: string, payload: IMusicTagGroupWriteDto): Promise<IMusicTagGroup | null> {
    isSaving.value = true

    try {
      const response = await musicTagGroupApi.updateGroup(id, payload)
      const group = mapTagGroupResponse(response)
      upsertGroup(group)
      handleApiSuccess(response)

      return group
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function deleteGroup(id: string): Promise<boolean> {
    isSaving.value = true

    try {
      const response = await musicTagGroupApi.deleteGroup(id)
      groups.value = groups.value.filter(group => group.id !== id)
      tags.value = tags.value.map(tag => (
        tag.group_id === id
          ? { ...tag, group_id: null, group: null }
          : tag
      ))
      handleApiSuccess(response)

      return true
    } catch (error) {
      handleApiError(error)
      return false
    } finally {
      isSaving.value = false
    }
  }

  async function createTag(payload: IMusicTagWriteDto): Promise<IMusicTag | null> {
    isSaving.value = true

    try {
      const response = await musicTagApi.createTag(payload)
      handleApiSuccess(response)
      await getTags()

      return mapTagResponse(response)
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateTag(id: string, payload: IMusicTagWriteDto): Promise<IMusicTag | null> {
    isSaving.value = true

    try {
      const response = await musicTagApi.updateTag(id, payload)
      handleApiSuccess(response)
      await getTags()

      return mapTagResponse(response)
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function deleteTag(id: string): Promise<boolean> {
    isSaving.value = true

    try {
      const response = await musicTagApi.deleteTag(id)
      handleApiSuccess(response)
      await getTags()

      return true
    } catch (error) {
      handleApiError(error)
      return false
    } finally {
      isSaving.value = false
    }
  }

  return {
    groups,
    tags,
    isGroupsLoading,
    isTagsLoading,
    isSaving,
    ungroupedTags,
    tagsByGroup,
    flattenTags,
    parentOptions,
    loadAll,
    getGroups,
    getTags,
    createGroup,
    updateGroup,
    deleteGroup,
    createTag,
    updateTag,
    deleteTag
  }
})
