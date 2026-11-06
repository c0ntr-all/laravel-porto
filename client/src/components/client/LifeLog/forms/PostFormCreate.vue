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
      <PostFormFilesUpload
        v-model="pendingAttachments"
        v-model:task="attachedTask"
        v-model:album="attachedAlbum"
      />
    </div>

    <div class="lifelog-post-form__tags q-pa-md">
      <PostFormCreateTags
        ref="formTagsRef"
        v-model="model"
      />
    </div>

    <div class="lifelog-post-form-actions flex wrap justify-between items-end q-pa-md">
      <div class="lifelog-post-form-actions__left">
        <div v-if="model.datetime" class="row items-center no-wrap q-gutter-xs">
          <AppDatetimeField v-if="!model.isNullTime" class="lifelog-post-form__datetime" v-model="model.datetime" />
          <AppDateField v-else class="lifelog-post-form__datetime" v-model="model.datetime" />
          <LifeLogNullTimeButton
            v-model="model.isNullTime"
            @update:model-value="onNullTimeChange"
          />
        </div>
      </div>
      <div class="lifelog-post-form-actions__right row items-center no-wrap q-gutter-sm">
        <q-btn
          flat
          no-caps
          color="grey-8"
          label="Сохранить черновик"
          :loading="submitMode === 'draft'"
          :disable="isSubmitting"
          @click="createPost(true)"
        />
        <q-btn
          unelevated
          no-caps
          color="primary"
          label="Отправить"
          :loading="submitMode === 'publish'"
          :disable="isSubmitting"
          @click="createPost(false)"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { nextTick, ref, watch } from 'vue'
import { applyIsNullTimeToDatetime, getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/postStore'
import {
  IPostAlbumSubject,
  IPostFormPendingAttachment,
  IPostModel,
  IPostTaskSubject
} from 'src/types'
import { splitPendingAttachments } from 'src/utils/attachment'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import AppDateField from 'src/components/default/AppDateField.vue'
import PostFormFilesUpload from 'src/components/client/LifeLog/forms/PostFormFilesUpload.vue'
import PostFormCreateTags from 'src/components/client/LifeLog/forms/PostFormCreateTags.vue'
import LifeLogNullTimeButton from 'src/components/client/LifeLog/forms/LifeLogNullTimeButton.vue'

interface IInputRef {
  resetValidation: () => void
}
interface ITagsRef {
  resetAvailableTags: () => void
}

// --- Store ---
const postStore = usePostStore()

const emit = defineEmits<{
  success: []
}>()

const getEmptyPostModel = (): IPostModel => {
  return {
    title: '',
    content: '',
    content_type: PostContentTypeEnum.DEFAULT,
    is_draft: false,
    tags: [],
    newTags: [],
    datetime: getCurrentDateTime(),
    isNullTime: false
  }
}

// --- Models ---
const model = ref<IPostModel>(getEmptyPostModel())
const pendingAttachments = ref<IPostFormPendingAttachment[]>([])
const attachedTask = ref<IPostTaskSubject | null>(null)
const attachedAlbum = ref<IPostAlbumSubject | null>(null)
const isSubmitting = ref(false)
const submitMode = ref<'draft' | 'publish' | null>(null)

// --- Refs ---
const titleRef = ref<IInputRef | null>(null)
const formTagsRef = ref<ITagsRef | null>(null)

watch(attachedTask, task => {
  if (!task) {
    if (model.value.content_type === PostContentTypeEnum.TASK) {
      model.value.content_type = PostContentTypeEnum.DEFAULT
      model.value.task_id = null
      model.value.task = null
    }
    return
  }

  model.value.content_type = PostContentTypeEnum.TASK
  model.value.task_id = Number(task.id)
  model.value.task = task
  model.value.album_id = null
  model.value.album = null
  if (!model.value.title?.trim()) {
    model.value.title = task.title
  }
})

watch(attachedAlbum, album => {
  if (!album) {
    if (model.value.content_type === PostContentTypeEnum.ALBUM) {
      model.value.content_type = PostContentTypeEnum.DEFAULT
      model.value.album_id = null
      model.value.album = null
    }
    return
  }

  model.value.content_type = PostContentTypeEnum.ALBUM
  model.value.album_id = Number(album.id)
  model.value.album = album
  model.value.task_id = null
  model.value.task = null
  if (!model.value.title?.trim()) {
    model.value.title = album.name
  }
})

// --- Methods ---
const createPost = async (asDraft: boolean) => {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  submitMode.value = asDraft ? 'draft' : 'publish'
  model.value.is_draft = asDraft
  const { files, galleryRefs } = splitPendingAttachments(pendingAttachments.value)
  const submission = postStore.createPost(model.value, files, galleryRefs)

  clearModel()
  pendingAttachments.value = []
  attachedTask.value = null
  attachedAlbum.value = null
  resetAvailableTags()
  emit('success')

  try {
    await submission
  } finally {
    isSubmitting.value = false
    submitMode.value = null
  }
}
const clearModel = () => {
  model.value = getEmptyPostModel()
  attachedTask.value = null
  attachedAlbum.value = null

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

const onNullTimeChange = (isNullTime: boolean | null) => {
  model.value.datetime = applyIsNullTimeToDatetime(
    model.value.datetime,
    Boolean(isNullTime)
  )
}
</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &__datetime {
    width: 240px;
  }

  &-actions {
    gap: 12px;
    background-color: #fbfbfb;

    &__right {
      margin-left: auto;
    }
  }
}
</style>
