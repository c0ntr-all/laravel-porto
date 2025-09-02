<template>
  <div class="comments">
    <div class="text-h6 q-mb-sm">Comments</div>
    <div class="comments-form q-mb-md">
      <q-input
        v-model="commentModel"
        class="q-mb-sm"
        placeholder="Write a comment..."
        filled
        autogrow
      />
      <q-btn @click="createComment" color="primary" label="Send"/>
    </div>
    <div v-if="commentsData" class="comments-list column q-gutter-sm">
      <q-card v-for="comment in commentsData" :key="comment.id">
        <q-card-section class="flex justify-between">
          <span>{{ comment.relationships.user.data.name }}</span>
          <time class="time">{{ comment.created_at }}</time>
        </q-card-section>
        <q-card-section>
          {{ comment.content }}
        </q-card-section>
      </q-card>
    </div>
    <p v-else class="text-grey-5">There are no comments!</p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { api } from 'src/boot/axios'
import { IComment, ICreateCommentResponse } from 'src/components/client/TaskManager/types'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { AxiosError } from 'axios'

const props = defineProps<{
  commentsData: IComment[],
  taskId: number,
}>()

const emit = defineEmits<{
  (e: 'created', value: IComment): void
}>()

const commentModel = ref<string>('')

const createComment = () => {
  api.post<ICreateCommentResponse>('v1/comments', {
    commentable_id: props.taskId,
    commentable_type: 'task',
    content: commentModel.value
  }).then((response) => {
    const responseData = response.data.data

    const newComment = {
      id: responseData.id,
      content: responseData.attributes.content,
      created_at: responseData.attributes.created_at,
      relationships: {
        user: {
          data: {
            id: responseData.relationships.user.data.id,
            name: response.data.included.filter(included => included.type === 'user' && included.id === responseData.relationships.user.data.id)[0].attributes.name as string
          }
        }
      }
    }

    emit('created', newComment)

    clearCommentModel()

    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const clearCommentModel = () => {
  commentModel.value = ''
}
</script>

<style scoped lang="scss">

</style>
