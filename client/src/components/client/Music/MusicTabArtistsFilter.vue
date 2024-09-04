<template>
  <div class="music-filter">
    <div class="text-h5 q-mb-sm">Filter</div>
    <div class="music-filter__params q-mb-md">
      <q-btn-toggle
        v-model="type"
        @click="setUnion"
        class="border-grey"
        no-caps
        rounded
        unelevated
        toggle-color="primary"
        color="white"
        text-color="primary"
        :options="[
          {label: 'Strict search', value: 'strict'},
          {label: 'Nested search', value: 'nested'}
        ]"
      />
      <div class="flex items-center">
        <span>ИЛИ</span>
        <q-toggle
          label="И"
          v-model="union"
          :disable="type !== 'strict'"
          color="primary"
          keep-color
        />
      </div>
    </div>
    <div class="flex q-mb-sm q-gutter-md">
      <q-select
        label="Select Styles"
        v-model="secondaryTagsModel"
        :options="secondaryTagsSelect"
        input-debounce="0"
        style="width: 100%"
        use-input
        use-chips
        multiple
        outlined
        dense
      />
      <q-select
        label="Select Genre"
        v-model="commonTagsModel"
        :options="commonTagsSelect"
        input-debounce="0"
        style="width: 100%"
        use-input
        use-chips
        multiple
        outlined
        dense
      />
      <div class="flex justify-end q-gutter-md" style="width: 100%">
        <q-btn class="q-mt-none q-ml-none" color="grey" label="Reset" @click="resetFilter" outline/>
        <q-btn class="q-mt-none" color="primary" label="Filter" @click="submitFilter"/>
      </div>
    </div>

    <q-inner-loading :showing="loading">
      <q-spinner-gears size="50px" color="primary"/>
    </q-inner-loading>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

interface Tag {
  label: string
  value: string
}

interface TagsResponse {
  items: {
    common: Record<string, Tag>
    secondary: Record<string, Tag>
  }
}

const emit = defineEmits<{
  resetFilter : []
  submitFilter: [payload: { filters: { music_tags: { tags: string[], type: string, union: boolean } } }]
}>()

const $q = useQuasar()

const type = ref<'strict' | 'nested'>('strict')
const union = ref<boolean>(true)
const commonTagsSelect = ref<Tag[]>([])
const secondaryTagsSelect = ref<Tag[]>([])
const commonTagsModel = ref<Tag[]>([])
const secondaryTagsModel = ref<Tag[]>([])
const loading = ref<boolean>(true)

const setUnion = () => {
  if (type.value === 'nested') {
    union.value = false
  }
}

const getTagsSelect = async (): Promise<void> => {
  loading.value = true
  await api.post<{ data: TagsResponse }>('music/tags/select').then(response => {
    const { data } = response.data

    commonTagsSelect.value = Object.values(data.items.common)
    secondaryTagsSelect.value = Object.values(data.items.secondary)
  }).catch(error => {
    $q.notify({
      type: 'negative',
      message: error.response?.data.message || 'Error'
    })
  }).finally(() => {
    loading.value = false
  })
}

const resetFilter = () => {
  commonTagsModel.value = []
  secondaryTagsModel.value = []
  type.value = 'strict'
  union.value = true

  emit('resetFilter')
}

const submitFilter = () => {
  const tags = commonTagsModel.value.concat(secondaryTagsModel.value).map(tag => tag.value)

  const filters = {
    music_tags: {
      tags,
      type: type.value,
      union: union.value
    }
  }
  emit('submitFilter', { filters })
}

onMounted(() => {
  getTagsSelect()
})
</script>

<style lang="scss">
.music-filter {
  position: relative;
}

.tags-toggle {
  border: 1px solid #027be3;
}
</style>
