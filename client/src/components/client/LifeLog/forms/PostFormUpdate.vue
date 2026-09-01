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
      <p>Управление существующими файлами</p>
      <TransitionGroup
        name="fade-scale"
        tag="div"
        class="post-form-existing-files"
      >
        <PostFormAttachmentPreview
          v-for="file in model.attachments"
          :key="file.id"
          :attachment="file"
          :deleted="file.is_deleted"
          removable
          @toggle-remove="handleSwitchRemoveFile(file)"
        />
      </TransitionGroup>
      <div v-if="model.attachments.length === 0" class="text-grey-6">
        Вложений нет
      </div>
    </div>

    <div class="lifelog-post-form__files q-pa-md">
      <p>Загрузить новые файлы</p>
      <PostFormFilesUpload v-model="newAttachmentsModel" />
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
            label="Сохранить"
            color="primary"
            :round="false"
            @click="updatePost"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, markRaw, onMounted, onUnmounted, ref, toRaw, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { unique } from 'radash'
import { getCurrentDateTime } from 'src/utils/datetime'
import { useTagStore } from 'src/stores/modules/tagStore'
import { usePostStore } from 'src/stores/modules/postStore'
import { INewTag, ITag } from 'src/types/tag'
import { IPost, IPostAttachmentWithState, IPostUpdateModel } from 'src/types'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import AppAddButton from 'src/components/default/AppAddButton.vue'
import PostFormFilesUpload from 'src/components/client/LifeLog/forms/PostFormFilesUpload.vue'
import PostFormAttachmentPreview from 'src/components/client/LifeLog/forms/PostFormAttachmentPreview.vue'
import AppDateField from 'src/components/default/AppDateField.vue'

interface IInputRef {
  resetValidation: () => void
}

const tagStore = useTagStore()
const { tags } = storeToRefs(tagStore)
const postStore = usePostStore()

const props = defineProps<{
  post: IPost
}>()

const model = ref<IPostUpdateModel>({
  title: '',
  content: '',
  tags: [],
  newTags: [],
  datetime: getCurrentDateTime(),
  attachments: [],
  isNullTime: true
})
const newAttachmentsModel = ref<File[]>([])
const originalPost = ref()
const availableTags = ref<ITag[]>([])

const titleRef = ref<IInputRef | null>(null)

const selectedTags = computed(() => {
  return unique(
    [...model.value.tags, ...model.value.newTags],
    (tag: ITag | INewTag) => 'id' in tag ? tag.id : tag.name
  )
})

const updatePost = async () => {
  await postStore.updatePost(
    props.post.id,
    model.value,
    originalPost.value,
    newAttachmentsModel.value
  ).then(updatedPost => {
    if (updatedPost) {
      mapPostToModel(updatedPost)
      clearAttachmentModel()
    }
  })
}

const getTagKey = (tag: ITag | INewTag) => {
  return 'id' in tag ? `tag-${tag.id}` : `newtag-${tag.name}`
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

const handleSwitchRemoveFile = (file: IPostAttachmentWithState) => {
  const index = model.value.attachments.findIndex(item => item.id === file.id)
  if (index === -1) {
    return
  }

  model.value.attachments[index].is_deleted = !file.is_deleted
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

const mapPostToModel = (post: IPost) => {
  const rawPost = toRaw(post)

  const preparedPost: IPostUpdateModel = {
    ...rawPost,
    newTags: [],
    datetime: rawPost.time ? rawPost.date + ' ' + rawPost.time : rawPost.date,
    isNullTime: !rawPost.time
  }

  model.value = preparedPost
  if (model.value.attachments.length) {
    model.value.attachments = toRaw(model.value.attachments.map((item: IPostAttachmentWithState) => {
      item.is_deleted = false
      return toRaw(item)
    }))
  }
  originalPost.value = structuredClone(preparedPost)
}

const clearAttachmentModel = () => {
  newAttachmentsModel.value = []
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

onMounted(() => {
  tagStore.getTags().then(() => {
    availableTags.value = [...tags.value]
  })

  mapPostToModel(props.post)
})

onUnmounted(() => {
  const rawPost = toRaw(props.post)

  model.value = {
    title: rawPost.title,
    content: rawPost.content,
    tags: rawPost.tags,
    newTags: [],
    datetime: rawPost.time ? `${rawPost.date} ${rawPost.time}` : rawPost.date,
    isNullTime: !!rawPost.time,
    attachments: []
  }

  newAttachmentsModel.value = []
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

.post-form-existing-files {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
  gap: 8px;
}
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.5s ease;
}

.fade-scale-enter-from {
  opacity: 0;
  transform: scale(0.8);
}

.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.5);
}

/* Важно: оставляем элемент на своем месте во время анимации */
.fade-scale-leave-active {
  position: static !important;
}
</style>
