<template>
  <q-card class="lifelog-posts-filter" flat>
    <q-card-section>
      <LifeLogTag
        v-for="tag in selectedTags"
        :key="tag.id"
        :tag="tag"
        @removed="handleRemoveTag"
        removable
      />
    </q-card-section>
    <q-card-section>
      <LifeLogTag
        v-for="tag in availableTags"
        :key="tag.id"
        :tag="tag"
        @selected="handleSelectTag"
        clickable
      />
    </q-card-section>
    <q-card-section class="flex justify-end">
      <q-select
        style="width: 100px"
        label="Mode"
        v-model="tagsModeModel"
        :options="tagsModes"
        outlined
        dense
      />
      <q-btn class="q-mt-none q-ml-md" color="grey" label="Reset" @click="resetFilter" outline/>
      <q-btn class="q-mt-none q-ml-md" color="primary" label="Filter" @click="submitFilter"/>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { useTagStore } from 'src/stores/modules/tagStore'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import { ITag } from 'src/types/tag'
import { unique } from 'radash'

const tagStore = useTagStore()

const emit = defineEmits<{
  (e: 'reset'): void
  (e: 'submit', value: string[]): void
}>()

const originalTags = ref<ITag[]>([])
const availableTags = ref<ITag[]>([])
const model = ref<ITag[]>([])
const tagsModeModel = ref<string>('and')
const tagsModes = ['or', 'and']

const selectedTags = computed(() => unique([...model.value], (tag: ITag) => tag.id))

const handleSelectTag = (tag: ITag) => {
  availableTags.value = availableTags.value.filter((t: ITag) => t.id !== tag.id)
  model.value.push(tag)
}
const handleRemoveTag = (tag: ITag) => {
  const isTagExists = model.value.some((t: ITag) => t.id === tag.id)
  if (isTagExists) {
    model.value = model.value.filter((t: ITag) => t.id !== tag.id)
    availableTags.value.push(tag)
  }
}

const resetFilter = () => {
  model.value = []
  availableTags.value = [...originalTags.value]
  emit('reset')
}

const submitFilter = () => {
  emit('submit', {
    tags: model.value,
    tags_mode: tagsModeModel.value
  })
}

onMounted(() => {
  tagStore.getTags().then((tags: ITag[]) => {
    originalTags.value = [...tags]
    availableTags.value = [...tags]
  })
})

</script>

<style lang="scss" scoped>
.lifelog-posts-filter {
  width: 100%;
}
</style>
