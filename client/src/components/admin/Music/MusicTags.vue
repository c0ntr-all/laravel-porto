<template>
  <div class="q-mb-md">
    <MusicTagsCreateNewTag @created="addNewTagToList"/>

    <q-input
      class="q-mb-md"
      ref="filterRef"
      v-model="filter"
      label="Search tags"
      dense
      filled
    >
      <template v-slot:append>
        <q-icon v-if="filter !== ''" name="clear" class="cursor-pointer" @click="resetFilter"/>
      </template>
    </q-input>

    <div class="text-h6 q-mb-xs">Main Tags</div>
    <template v-if="loading">
      loading...
    </template>
    <template v-else>
      <MusicTagsList :nodes="baseTags"/>
    </template>
  </div>

  <div class="q-mb-md">
    <div class="text-h6 q-mb-xs">Secondary Tags</div>
    <template v-if="loading">
      loading...
    </template>
    <template v-else>
      <MusicTagsList :nodes="secondaryTags"/>
    </template>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, provide, ref } from 'vue'
import { api } from 'src/boot/axios'
import { AxiosError } from 'axios'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import MusicTagsCreateNewTag from 'src/components/admin/Music/MusicTagsCreateNewTag.vue'
import MusicTagsList from 'src/components/admin/Music/MusicTagsList.vue'
import { insertIntoTree } from 'src/utils/arrayHelper'
import { ITag } from 'src/components/admin/Music/types'

interface IResponseTag {
  type: string
  id: string
  attributes: {
    name: string
    content: string | null
    is_base: boolean
    parent_id: string | null
    tags: ITag[]
  }
  relationships: any
}

interface IGetTagsResponse {
  data: IResponseTag[]
  included: any
}

const loading = ref(true)
const baseTags = ref<ITag[]>([])
const secondaryTags = ref<ITag[]>([])
const filterRef = ref<HTMLInputElement | null>(null)
const filter = ref('')

const resetFilter = () => {
  filter.value = ''
  filterRef.value?.focus()
}

const getTags = async () => {
  await api.get<IGetTagsResponse>('v1/music/tags').then(response => {
    baseTags.value = prepareTags(response.data) as ITag[]
    secondaryTags.value = prepareTags(response.data, false) as ITag[]
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  }).finally(() => {
    loading.value = false
  })
}

const prepareTags = (responseData: IGetTagsResponse, isBase: boolean = true) => {
  return responseData.data.filter((tag: IResponseTag) => tag.attributes.is_base === isBase).map((responseTag: IResponseTag) => {
    return {
      id: responseTag.id,
      name: responseTag.attributes.name,
      content: responseTag.attributes.content,
      is_base: responseTag.attributes.is_base,
      parent_id: responseTag.attributes.parent_id,
      tags: getIncluded<ITag>('tags.*', responseTag.relationships, responseData.included, false)
    }
  })
}

const addNewTagToList = (tag: ITag) => {
  const isBase = tag.is_base
  if (tag.parent_id) {
    insertIntoTree(
      isBase ? baseTags.value : secondaryTags.value,
      tag.parent_id,
      tag,
      'tags'
    )
  } else {
    const mainTree = isBase ? baseTags.value : secondaryTags.value

    mainTree.push(tag)
    mainTree.sort((a, b) => a.name.localeCompare(b.name))
  }
}

provide('addNewTagToList', addNewTagToList)

onMounted(() => {
  getTags()
})
</script>

<style lang="scss" scoped>
.tag {
  &__actions {
    margin-left: 5px;
  }

  &-dialog {
    min-width: 500px;
  }
}
</style>
