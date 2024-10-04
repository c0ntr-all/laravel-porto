<template>
  <q-dialog v-model="show">
    <q-card class="tag-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Are you sure?</div>
        <q-space/>
        <q-btn icon="close" flat round dense v-close-popup/>
      </q-card-section>

      <q-card-section>
        <p>Delete tag <b>{{ tag.name }}</b></p>
      </q-card-section>

      <q-card-section>
        <q-btn @click="deleteTag" color="primary">Delete</q-btn>
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

interface DeleteTagApiResponse {
  data: {
    id: string
    attributes: {
      title: string
      created_at: string
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
  (e: 'deleted', value: string): void
}>()
const show = ref(props.modelValue)
const tag = ref<TagProp>(props.tag)

const deleteTag = async () => {
  await api.delete<DeleteTagApiResponse>(`v1/music/tags/${tag.value.id}`)
    .then(response => {
      emit('deleted', tag.value.id)

      handleApiSuccess(response)
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      show.value = false
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
