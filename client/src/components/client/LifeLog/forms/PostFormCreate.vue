<template>
  <div class="lifelog-post-form">
    <div class="lifelog-post-form__title q-px-md q-pt-md q-pb-sm">
      <q-input
        ref="titleRef"
        v-model="model.title"
        class="q-pa-none"
        label="Title"
        :rules="[val => !!val || 'Field is required']"
        dense
        outlined
      />
    </div>
    <div class="lifelog-post-form__content q-px-md q-pt-md q-pb-sm">
      <q-editor
        v-model="model.content"
        min-height="5rem"
        style="border-radius: 0"
      />
    </div>
    <div class="lifelog-post-form__files q-pa-md">
      <PostFormFilesUpload v-model="attachmentModel" />
    </div>

    <div class="lifelog-post-form__tags q-pa-md">
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
      <p>Последние:</p>
      <LifeLogTag
        v-for="tag in availableTags"
        :key="tag.id"
        :tag="tag"
        @selected="handleSelectTag"
        clickable
      />
    </div>

    <div class="lifelog-post-form-actions flex justify-between q-pa-md">
      <div class="lifelog-post-form-actions__left">
        <div v-if="model.datetime" class="lifelog-post-form__action">
          <AppDatetimeField v-model="model.datetime" />
        </div>
      </div>
      <div class="lifelog-post-form-actions__right">
        <div class="lifelog-post-form__action">
          <q-btn
            label="Отправить"
            color="primary"
            :round="false"
            @click="createPost"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, markRaw, nextTick, onMounted, ref, toRaw } from 'vue'
import { storeToRefs } from 'pinia'
import { unique } from 'radash'
import { getCurrentDateTime } from 'src/utils/datetime'
import { useTagStore } from 'src/stores/modules/tagStore'
import { usePostStore } from 'src/stores/modules/LifeLog/postStore'
import { INewTag, ITag } from 'src/types/tag'
import { IPost, IPostModel } from 'src/types'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import AppAddButton from 'src/components/default/AppAddButton.vue'
import PostFormFilesUpload from 'src/components/client/LifeLog/forms/PostFormFilesUpload.vue'

interface IInputRef {
  resetValidation: () => void
}

// --- Store ---
const tagStore = useTagStore()
const { tags } = storeToRefs(tagStore)
const postStore = usePostStore()

// --- Props ---
defineProps<{
  post?: IPost
}>()

// --- Models ---
const model = ref<IPostModel>({
  title: '',
  content: '',
  tags: [],
  newTags: [],
  datetime: getCurrentDateTime()
})
const attachmentModel = ref<File[]>([])

// --- State ---
const originalPost = ref({})
const availableTags = ref<ITag[]>([])

// --- Refs ---
const titleRef = ref<IInputRef | null>(null)

// --- Computed ---
const selectedTags = computed(() => {
  return unique(
    [...model.value.tags, ...model.value.newTags],
    (tag: ITag | INewTag) => 'id' in tag ? tag.id : tag.name
  )
})

// --- Methods ---
const createPost = async () => {
  await postStore.createPost(model.value, attachmentModel.value).then(() => {
    clearModel()
    // clearAttachmentModel()
    resetAvailableTags()
  })
}
const getTagKey = (tag: ITag | INewTag) => {
  return 'id' in tag ? `tag-${tag.id}` : `newtag-${tag.name}`
}
const resetAvailableTags = () => {
  availableTags.value = [...tags.value]
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
const getEmptyPostModel = (): IPostModel => {
  return {
    title: '',
    content: '',
    tags: [],
    newTags: [],
    datetime: getCurrentDateTime()
  }
}
const clearModel = () => {
  model.value = getEmptyPostModel()

  nextTick(() => {
    if (titleRef.value) {
      titleRef.value.resetValidation()
    }
  })
}

// --- Lifecycle ---
onMounted(() => {
  tagStore.getTags().then(() => {
    availableTags.value = [...tags.value]
  })
  model.value = getEmptyPostModel()
  originalPost.value = structuredClone(toRaw(model.value))
})
</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &-actions {
    background-color: #fbfbfb;
  }
}
</style>
