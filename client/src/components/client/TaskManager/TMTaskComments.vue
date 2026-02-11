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
        name="comment"
      />
      <q-btn @click="createComment" color="primary" label="Send"/>
    </div>
    <div v-if="comments.length" class="comments-list column q-gutter-sm">
      <TMTaskComment
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
      />
    </div>
    <p v-else class="text-grey-5">There are no comments!</p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { IComment } from 'src/types/TaskManager/task'
import TMTaskComment from 'src/components/client/TaskManager/TMTaskComment.vue'
import { useTaskStore } from 'src/stores/modules/taskStore'

const taskStore = useTaskStore()

const props = defineProps<{
  comments: IComment[],
  taskId: string,
}>()

const commentModel = ref<string>('')

async function createComment() {
  await taskStore.createComment({
    commentable_id: props.taskId,
    commentable_type: 'tm_tasks',
    content: commentModel.value
  }).then(() => {
    clearCommentModel()
  })
}

const clearCommentModel = () => {
  commentModel.value = ''
}
</script>

<style scoped lang="scss">

</style>
