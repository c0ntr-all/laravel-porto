<template>
  <div class="lifelog-post-form">
    <div class="lifelog-post-form__title q-px-md q-pt-md q-pb-sm">
      <q-input
        v-model="model.title"
        class="q-pa-none"
        label="Заголовок"
        :rules="[val => !!val?.trim() || 'Обязательное поле']"
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

    <div class="lifelog-post-form__tags q-pa-md">
      <PostFormCreateTags
        ref="formTagsRef"
        v-model="model"
      />
    </div>

    <div class="lifelog-post-form-actions flex justify-between q-pa-md">
      <div class="lifelog-post-form-actions__left">
        <div class="flex">
          <AppDatetimeField
            v-if="!model.isNullTime"
            class="lifelog-post-form__datetime"
            v-model="model.datetime"
          />
          <AppDateField
            v-else
            class="lifelog-post-form__datetime"
            v-model="model.datetime"
          />
          <q-checkbox
            v-model="model.isNullTime"
            name="не учитывать время"
            label="не учитывать время"
          />
        </div>
      </div>
      <div class="lifelog-post-form-actions__right">
        <q-btn
          label="Сохранить"
          color="primary"
          no-caps
          :loading="isSubmitting"
          :disable="isSubmitting"
          :round="false"
          @click="submit"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref, toRaw, watch } from 'vue'
import { getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/postStore'
import { IPost, IPostUpdateModel } from 'src/types'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import AppDateField from 'src/components/default/AppDateField.vue'
import PostFormCreateTags from 'src/components/client/LifeLog/forms/PostFormCreateTags.vue'

interface ITagsRef {
  resetAvailableTags: () => void
}

const props = defineProps<{
  post: IPost
}>()

const emit = defineEmits<{
  success: []
}>()

const postStore = usePostStore()

const model = ref<IPostUpdateModel>({
  title: '',
  content: '',
  content_type: PostContentTypeEnum.MOVIE,
  tags: [],
  newTags: [],
  datetime: getCurrentDateTime(),
  isNullTime: false,
  attachments: []
})

const originalPost = ref<IPostUpdateModel | null>(null)
const isSubmitting = ref(false)
const formTagsRef = ref<ITagsRef | null>(null)

function mapPostToModel(post: IPost): IPostUpdateModel {
  const rawPost = toRaw(post)

  return {
    title: rawPost.title ?? '',
    content: rawPost.content ?? '',
    content_type: rawPost.content_type ?? PostContentTypeEnum.MOVIE,
    tags: [...(rawPost.tags ?? [])],
    newTags: [],
    datetime: rawPost.time ? `${rawPost.date} ${rawPost.time}` : rawPost.date,
    isNullTime: !rawPost.time,
    attachments: []
  }
}

async function submit() {
  if (isSubmitting.value || !originalPost.value) {
    return
  }

  isSubmitting.value = true

  try {
    const updatedPost = await postStore.updatePost(
      props.post.id,
      model.value,
      props.post,
      []
    )

    if (updatedPost) {
      const nextModel = mapPostToModel(updatedPost)
      model.value = nextModel
      originalPost.value = structuredClone(nextModel)
      formTagsRef.value?.resetAvailableTags()
      emit('success')
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(() => model.value.isNullTime, newValue => {
  const onlyDate = model.value.datetime.split(' ')[0]
  if (newValue) {
    model.value.datetime = onlyDate
  } else {
    model.value.datetime = `${onlyDate} 00:00`
  }
})

onMounted(() => {
  const initial = mapPostToModel(props.post)
  model.value = initial
  originalPost.value = structuredClone(initial)
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
