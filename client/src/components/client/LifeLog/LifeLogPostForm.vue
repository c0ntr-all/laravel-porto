<template>
  <div class="lifelog-post-form">
    <div class="lifelog-post-form__title">
      <q-input
        ref="titleRef"
        v-model="model.title"
        label="Title"
        :rules="[val => !!val || 'Field is required']"
        dense
        outlined
      />
    </div>
    <div class="lifelog-post-form__content">
      <q-editor
        v-model="model.content"
        min-height="5rem"
        style="border-radius: 0"
      />
    </div>

    <div class="lifelog-post-form__tags q-pa-md">
      <p>Выбранные:</p>
      <LifeLogTag
        v-for="tag in model.tags"
        :key="tag.id"
        :tag="tag"
        @removed="handleRemoveTag"
        removable
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
        <div class="lifelog-post-form__action">
          <AppDatetimeField v-model="model.datetime" />
        </div>
      </div>
      <div class="lifelog-post-form-actions__right">
        <div class="lifelog-post-form__action">
          <q-btn
            label="Отправить"
            color="primary"
            :round="false"
            @click="createPost()"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { nextTick, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { getCurrentDateTime } from 'src/utils/datetime'
import { useTagStore } from 'src/stores/modules/tagStore'
import { usePostStore } from 'src/stores/modules/LifeLog/postStore'
import { ITag } from 'src/types/tag'
import { IPostModel } from 'src/types'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'

interface IInputRef {
  resetValidation: () => void
}

const tagStore = useTagStore()
const postStore = usePostStore()

const { tags } = storeToRefs(tagStore)
const availableTags = ref<ITag[]>([])

const model = ref<IPostModel>({
  title: '',
  content: '',
  tags: [],
  datetime: getCurrentDateTime()
})
const titleRef = ref<IInputRef | null>(null)

const createPost = () => {
  postStore.createPost(model.value).then(() => {
    clearModel()
    resetAvailableTags()
  })
}

const clearModel = () => {
  model.value.title = ''
  model.value.content = ''
  model.value.datetime = getCurrentDateTime()
  model.value.tags = []

  nextTick(() => {
    if (titleRef.value) {
      titleRef.value.resetValidation()
    }
  })
}

const resetAvailableTags = () => {
  availableTags.value = [...tags.value]
}

const handleSelectTag = (tag: ITag) => {
  if (!model.value.tags.some((t: ITag) => t.id === tag.id)) {
    availableTags.value = availableTags.value.filter((t: ITag) => t.id !== tag.id)
    model.value.tags.push(tag)
  }
}

const handleRemoveTag = (tag: ITag) => {
  if (model.value.tags.some((t: ITag) => t.id === tag.id)) {
    model.value.tags = model.value.tags.filter((t: ITag) => t.id !== tag.id)
    availableTags.value.push(tag)
  }
}

onMounted(() => {
  tagStore.getTags().then(() => {
    availableTags.value = [...tags.value]
  })
})

</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &__tags {
    border-right: 1px solid rgba(0, 0, 0, 0.12);
    border-left: 1px solid rgba(0, 0, 0, 0.12);
  }

  &-actions {
    background-color: #fbfbfb;
    border-right: 1px solid rgba(0, 0, 0, 0.12);
    border-left: 1px solid rgba(0, 0, 0, 0.12);
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  }
}
</style>
