<template>
  <q-dialog v-model="show">
    <q-card class="tag-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Edit tag <b>{{ model.name }}</b></div>
        <q-space/>
        <q-btn icon="close" flat round dense v-close-popup/>
      </q-card-section>

      <q-card-section>
        <div class="q-gutter-md">
          <q-input
            v-model="model.name"
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
        <q-btn @click="updateTag" color="primary">Save</q-btn>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { ref, watch, watchEffect } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'

interface TagProp {
  id: string
  name: string
  content: string | null
  is_base: boolean
  parentTag?: TagProp
}

interface TagModel {
  name: string
  content: string | null
  is_base: boolean
}

interface UpdateTagApiResponse {
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
  tag: TagProp
}>()
const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()
const show = ref(props.modelValue)
const model = ref<TagModel>({
  name: props.tag.name,
  content: props.tag.content,
  is_base: props.tag.is_base
})

const updateTag = async () => {
  await api.patch<UpdateTagApiResponse>(`v1/music/tags/${props.tag.id}`, {
    name: model.value.name,
    content: model.value.content
  }).then(response => {
    handleApiSuccess(response)
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
