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
      <LifeLogTag
        v-for="tag in tags"
        :key="tag.id"
        :tag="tag"
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
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'

interface IInputRef {
  resetValidation: () => void
}

const tagStore = useTagStore()
const postStore = usePostStore()

const { tags } = storeToRefs(tagStore)

const model = ref({
  title: '',
  content: '',
  datetime: getCurrentDateTime()
})
const titleRef = ref<IInputRef | null>(null)

const createPost = () => {
  postStore.createPost(model.value).then(() => {
    clearModel()
  })
}

const clearModel = () => {
  model.value.title = ''
  model.value.content = ''
  model.value.datetime = getCurrentDateTime()

  nextTick(() => {
    if (titleRef.value) {
      titleRef.value.resetValidation()
    }
  })
}

onMounted(() => {
  tagStore.getTags()
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
