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
        class="row q-gutter-x-xs"
        style="border-left: 2px solid black"
      >
        <div
          class="lifelog-post-form__files-item"
          v-for="file in model.attachments"
          :key="file.id"
        >
          <div class="lifelog-post-form__file" :class="{'lifelog-post-form__file--deleted': file.is_deleted}">
            <div class="lifelog-post-form__file-remove" :class="file.is_deleted ? 'text-red' : 'text-grey'">
              <q-icon
                size="sm"
                name="cancel"
                role="button"
                class="file-remove"
                :class="{'file-remove--restore': file.is_deleted}"
                @click="handleSwitchRemoveFile(file)"
              />
            </div>
            <img :src="file.list_thumb_path" alt="">
          </div>
        </div>
      </TransitionGroup>
      <div v-if="model.attachments.length === 0" style="color: #ccc">
        There are no attached files
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
          <AppDatetimeField v-model="model.datetime" />
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
import { computed, markRaw, onMounted, onUnmounted, ref, toRaw } from 'vue'
import { storeToRefs } from 'pinia'
import { unique } from 'radash'
import { getCurrentDateTime } from 'src/utils/datetime'
import { useTagStore } from 'src/stores/modules/tagStore'
import { usePostStore } from 'src/stores/modules/LifeLog/postStore'
import { INewTag, ITag } from 'src/types/tag'
import { IPost, IPostUpdateModel } from 'src/types'
import { IAttachmentWithState } from 'src/types/attachment'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import AppAddButton from 'src/components/default/AppAddButton.vue'
import PostFormFilesUpload from 'src/components/client/LifeLog/forms/PostFormFilesUpload.vue'

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
  attachments: []
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

const handleSwitchRemoveFile = (file: IAttachmentWithState) => {
  const index = model.value.attachments.findIndex(x => x.id === file.id)
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

  const preparedPost = {
    ...rawPost,
    newTags: []
  }

  model.value = preparedPost
  if (model.value.attachments.length) {
    // Подготовка состояния файлов на случай удаления
    model.value.attachments = toRaw(model.value.attachments.map((item: IAttachmentWithState) => {
      item.is_deleted = false
      return toRaw(item)
    }))
  }
  originalPost.value = structuredClone(preparedPost)
}

const clearAttachmentModel = () => {
  newAttachmentsModel.value = []
}

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
    datetime: rawPost.datetime,
    attachments: []
  }

  newAttachmentsModel.value = []
})

</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &__file {
    position: relative;

    &-remove {
      position: absolute;
      top: -4px;
      right: -4px;
      border-radius: 50%;
      background: #fff;
      padding: 1px;
      z-index: 2;
    }
    img {
      width: 100px;
      height: auto;
    }
    &--deleted {
      img {
        filter: brightness(45%);
        transition: filter 0.3s ease;
      }
    }
  }

  &-actions {
    background-color: #fbfbfb;
  }
}
.file-remove {
  cursor: pointer;
  z-index: 1;
  outline: 0 !important;
  border: 0;
  color: inherit;
  background: transparent;
  padding: 0;
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
