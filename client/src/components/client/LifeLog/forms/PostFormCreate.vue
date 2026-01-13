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
        <div v-if="model.datetime" class="flex">
          <AppDatetimeField v-if="!model.isNullTime" class="lifelog-post-form__datetime" v-model="model.datetime" />
          <AppDateField v-else class="lifelog-post-form__datetime" v-model="model.datetime" />
          <q-checkbox
            v-model="model.isNullTime"
            name="не учитывать время"
            label="не учитывать время"
          />
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
import { nextTick, onMounted, ref, toRaw, watch } from 'vue'
import { getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/postStore'
import { IPost, IPostModel } from 'src/types'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import AppDateField from 'src/components/default/AppDateField.vue'
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

const getEmptyPostModel = (): IPostModel => {
  return {
    title: '',
    content: '',
    tags: [],
    newTags: [],
    datetime: getCurrentDateTime(),
    isNullTime: false
  }
}

// --- Models ---
const model = ref<IPostModel>(getEmptyPostModel())
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

watch(() => model.value.isNullTime, newValue => {
  const onlyDate = model.value.datetime.split(' ')[0]
  if (newValue) {
    // Delete time for no time input
    model.value.datetime = onlyDate
  } else {
    // Restore time for preventing date clearing in calendar
    model.value.datetime = `${onlyDate} 00:00`
  }
})

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

  &__datetime {
    width: 240px;
  }

  &-actions {
    background-color: #fbfbfb;
  }
}
</style>
