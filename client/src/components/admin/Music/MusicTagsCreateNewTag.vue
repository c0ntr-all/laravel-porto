<template>
  <q-btn
    class="q-mb-md"
    @click="openDialog"
    icon="add"
    label="Create tag"
    size="md"
    color="primary"
    dense
  />

  <q-dialog v-model="show">
    <q-card class="tag-dialog" style="width: 100%">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Create tag</div>
        <q-space/>
        <q-btn icon="close" flat round dense v-close-popup/>
      </q-card-section>

      <q-card-section>
        <div class="q-gutter-md">
          <q-input
            v-model="model.name"
            placeholder="Name"
            ref="tagNameRef"
            dense
            filled
          />
          <q-input
            v-model="model.content"
            placeholder="Description"
            type="textarea"
            dense
            filled
          />
          <q-checkbox
            v-model="model.is_base"
            label="Main tag"
            color="primary"
            left-label
            dense
          />
        </div>
      </q-card-section>

      <q-card-section>
        <q-btn @click="createTag" color="primary">Create</q-btn>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { nextTick, ref } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'

interface Tag {
  id: string
  name: string
  content: string | null
  is_base: boolean
  parent_id: string | null
  tags: Tag[]
}

interface TagModel {
  name?: string
  content?: string
  is_base: boolean
}

interface CreateTagApiResponse {
  data: {
    type: string
    id: string
    attributes: {
      name: string
      content: string | null
      is_base: boolean
      parent_id: number | null
    }
    relationships: {
      tags: {
        data: any[]
      }
    }
  }
}

const emit = defineEmits<{
  (e: 'created', tag: Tag): void
}>()

const show = ref<boolean>(false)
const model = ref<TagModel>({ is_base: true })
const tagNameRef = ref<HTMLElement | null>(null)

const createTag = async () => {
  const postData = {
    name: model.value.name,
    content: model.value.content
  }

  await api.post<CreateTagApiResponse>('v1/music/tags', postData).then(response => {
    const newTagResponse = response.data.data

    const newTag = {
      id: newTagResponse.id,
      name: newTagResponse.attributes.name,
      content: newTagResponse.attributes.content,
      is_base: newTagResponse.attributes.is_base,
      parent_id: null,
      tags: []
    }

    emit('created', newTag)

    handleApiSuccess(response)

    clearTagModel()
    show.value = false
  }).catch(error => {
    handleApiError(error)
  })
}

const clearTagModel = () => {
  model.value = {
    name: '',
    content: '',
    is_base: true
  }
}

const openDialog = () => {
  show.value = true
  nextTick(() => {
    if (tagNameRef.value) {
      tagNameRef.value.querySelector('input')?.focus()
    }
  })
}
</script>

<style scoped>

</style>
