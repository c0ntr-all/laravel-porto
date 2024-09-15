<template>
  <q-btn type="primary" :to="'/music'">Вернуться назад</q-btn>
  <div class="tag-head">
    <div class="tag-head__info">
      <div class="text-h5">{{ tag?.name }}</div>
    </div>
  </div>
  <div class="tag-content">
    {{ tag?.content }}
  </div>
</template>
<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { AxiosError } from 'axios'

interface Tag {
  id: string
  name: string
  content: string
}

interface ResponseTag {
  type: string
  id: string
  attributes: {
    name: string
    content: string
  }
}

interface GetTagApiResponse {
  data: ResponseTag
}

const $q = useQuasar()

const props = defineProps<{
  slug: string
}>()

const loading = ref<boolean>(true)
const tag = ref<Tag | null>(null)

const getTag = async (slug: string): Promise<void> => {
  await api.get<GetTagApiResponse>(`v1/music/tags/${slug}`).then(response => {
    const { id, attributes } = response.data.data
    tag.value = { id, ...attributes }
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  }).finally(() => {
    loading.value = false
  })
}

const handleApiError = (error: AxiosError<{ message: string }>) => {
  $q.notify({
    type: 'negative',
    message: error.response?.data.message || 'Error'
  })
}

onMounted(() => {
  getTag(props.slug)
})
</script>

<style lang="scss" scoped>
</style>
