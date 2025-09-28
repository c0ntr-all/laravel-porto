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
import { nextTick, ref } from 'vue'
import { getCurrentDateTime } from 'src/utils/datetime'
import { usePostStore } from 'src/stores/modules/LifeLog/postStore'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'

interface IInputRef {
  resetValidation: () => void
}

const postStore = usePostStore()

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

</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &-actions {
    background-color: #fbfbfb;
    border-right: 1px solid rgba(0, 0, 0, 0.12);
    border-left: 1px solid rgba(0, 0, 0, 0.12);
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  }
}
</style>
