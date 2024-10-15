<template>
  <q-dialog v-model="show">
    <q-card class="tag-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Create new child tag for <b>{{ props.tag.parentTag.name }}</b></div>
        <q-space/>
        <q-btn icon="close" flat round dense v-close-popup/>
      </q-card-section>

      <q-card-section>
        <div class="q-gutter-md">
          <q-input
            v-model="model.name"
            ref="tagNameRef"
            placeholder="Name"
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
        </div>
      </q-card-section>

      <q-card-section>
        <q-btn @click="createTag" color="primary">Create</q-btn>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { inject, ref, watch, watchEffect } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { ITag } from 'src/components/admin/Music/types'

interface TagPropCreateChild {
  id: string
  name: string
  content: string | null
  is_base: boolean
  parentTag: TagPropCreateChild
}

interface TagModel {
  name: string
  content: string | null
  is_base: boolean
}

interface CreateTagApiResponse {
  data: {
    id: string
    attributes: {
      name: string
      content: string | null
      is_base: boolean
      parent_id: string
    }
  }
  meta: {
    message: string
  }
}

const props = defineProps<{
  modelValue: any,
  tag: TagPropCreateChild
}>()
const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()
const show = ref(props.modelValue)
const model = ref<TagModel>({
  name: '',
  content: '',
  is_base: props.tag.parentTag.is_base || true
})

const addNewTagToList = inject<{(tag: ITag): void}>('addNewTagToList')!

const createTag = async () => {
  const postData = {
    ...model.value,
    parent_id: props.tag.parentTag.id
  }

  await api.post<CreateTagApiResponse>('v1/music/tags', postData).then(response => {
    const responseTag = response.data.data

    handleApiSuccess(response)

    addNewTagToList({
      id: responseTag.id,
      name: responseTag.attributes.name,
      content: responseTag.attributes.content,
      is_base: responseTag.attributes.is_base,
      parent_id: responseTag.attributes.parent_id,
      tags: []
    })

    show.value = false
  }).catch(error => {
    handleApiError(error)
  })
}

watchEffect(() => {
  show.value = props.modelValue
})
watch(show, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})
</script>

<style lang="scss" scoped>

</style>
