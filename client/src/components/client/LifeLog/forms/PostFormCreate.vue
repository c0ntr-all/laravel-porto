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
      <PostFormCreateTags
        ref="formTagsRef"
        v-model="model"
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
import { nextTick, onMounted, ref, toRaw } from 'vue'
import { getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/LifeLog/postStore'
import { IPost, IPostModel } from 'src/types'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import PostFormFilesUpload from 'src/components/client/LifeLog/forms/PostFormFilesUpload.vue'
import PostFormCreateTags from 'src/components/client/LifeLog/forms/PostFormCreateTags.vue'

interface IInputRef {
  resetValidation: () => void
}
interface ITagsRef {
  resetAvailableTags: () => void
}

// --- Store ---
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

// --- Refs ---
const titleRef = ref<IInputRef | null>(null)
const formTagsRef = ref<ITagsRef | null>(null)

// --- Methods ---
const createPost = async () => {
  await postStore.createPost(model.value, attachmentModel.value).then(() => {
    clearModel()
    clearAttachmentModel()
    resetAvailableTags()
  })
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
const clearAttachmentModel = () => {
  attachmentModel.value = []
}
const clearModel = () => {
  model.value = getEmptyPostModel()

  nextTick(() => {
    if (titleRef.value) {
      titleRef.value.resetValidation()
    }
  })
}

const resetAvailableTags = () => {
  if (formTagsRef.value) {
    formTagsRef.value.resetAvailableTags()
  }
}

// --- Lifecycle ---
onMounted(() => {
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
