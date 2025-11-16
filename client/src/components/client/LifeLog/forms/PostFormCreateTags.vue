<template>
  <p>Выбранные:</p>
  <LifeLogTag
    v-for="tag in selectedTags"
    :key="getTagKey(tag)"
    :tag="tag"
    @removed="handleRemoveTag"
    removable
  />
  <AppAddButton
    @created="handleAddTag"
  />
  <div class="q-py-md">
    <q-btn-toggle
      v-model="tagsModeModel"
      toggle-color="primary"
      :options="[
            {label: 'Наиболее используемые', value: TagsModeEnum.MOST_USED},
            {label: 'Последние', value: TagsModeEnum.LAST}
          ]"
      :disable="tagStore.isLoading"
      dense
      no-caps
    />
  </div>
  <div class="relative-position">
    <LifeLogTag
      v-for="tag in availableTags"
      :key="tag.id"
      :tag="tag"
      @selected="handleSelectTag"
      clickable
    />

    <q-inner-loading :showing="tagStore.isLoading">
      <q-spinner-gears size="50px" color="primary" />
    </q-inner-loading>
  </div>
</template>

<script lang="ts" setup>
import { computed, markRaw, onMounted, ref, watch } from 'vue'
import { unique } from 'radash'
import { INewTag, ITag } from 'src/types/tag'
import { IPostModel } from 'src/types'
import { TagsModeEnum } from 'src/enums/LifeLog/TagsModeEnum'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import AppAddButton from 'src/components/default/AppAddButton.vue'
import { useTagStore } from 'src/stores/modules/tagStore'

// --- Store ---
const tagStore = useTagStore()

// --- Models ---
const model = defineModel<IPostModel>({
  required: true
})
const tagsModeModel = ref(TagsModeEnum.MOST_USED)

// --- State ---
const originalTags = ref<ITag[]>([])
const availableTags = ref<ITag[]>([])

// --- Computed ---
const selectedTags = computed(() => {
  return unique(
    [...model.value.tags, ...model.value.newTags],
    (tag: ITag | INewTag) => 'id' in tag ? tag.id : tag.name
  )
})

// --- Methods ---
const getTags = (tagsMode: TagsModeEnum|null = null) => {
  // TODO: Сделать проверку тегов на предмет уже выбранных
  tagStore.getTags(tagsMode).then((tags: ITag[]) => {
    availableTags.value = [...tags]
    originalTags.value = [...tags]
  })
}
const handleAddTag = (tagName: string) => {
  const existingTag = availableTags.value.find(t => t.name === tagName)

  if (existingTag) {
    handleSelectTag(existingTag)
  } else {
    model.value.newTags.push(markRaw({
      name: tagName
    }))
  }
}
const handleSelectTag = (tag: ITag) => {
  if (!model.value.tags.some((t: ITag) => t.id === tag.id)) {
    availableTags.value = availableTags.value.filter((t: ITag) => t.id !== tag.id)
    model.value.tags.push(tag)
  }
}
const handleRemoveTag = (tag: ITag | INewTag) => {
  if ('id' in tag) {
    const isTagExists = model.value.tags.some((t: ITag) => t.id === tag.id)
    if (isTagExists) {
      model.value.tags = model.value.tags.filter((t: ITag) => t.id !== tag.id)
      availableTags.value.push(tag)
    }
  } else {
    const isTagExists = model.value.newTags.some(t => t.name === tag.name)
    if (isTagExists) {
      model.value.newTags = model.value.newTags.filter(t => t.name !== tag.name)
    }
  }
}

const getTagKey = (tag: ITag | INewTag) => {
  return 'id' in tag ? `tag-${tag.id}` : `newtag-${tag.name}`
}

defineExpose({
  resetAvailableTags: () => {
    availableTags.value = [...originalTags.value]
  }
})

watch(tagsModeModel, (newValue: TagsModeEnum) => {
  getTags(newValue)
})

onMounted(() => {
  getTags(tagsModeModel.value)
})
</script>

<style lang="scss" scoped>

</style>
