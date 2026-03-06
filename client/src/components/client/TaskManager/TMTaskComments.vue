<template>
  <div class="comments">
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
    <q-inner-loading :showing="isCommentsLoading">
      <q-spinner-gears size="50px" color="primary" />
    </q-inner-loading>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { IComment } from 'src/types'
import TMTaskComment from 'src/components/client/TaskManager/TMTaskComment.vue'
import { useTaskStore } from 'src/stores/modules/taskStore'

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string,
}>()

const commentModel = ref<string>('')

const isCommentsLoading = ref(true)
const comments = computed<IComment[]>(() => {
  // TODO: переделать под Enum
  return Object.values(taskStore.comments.byId).filter(comment => {
    return comment.commentable_id === props.taskId && comment.commentable_type === 'tm_tasks'
  })
})
async function loadComments() {
  await taskStore.getComments({
    commentable_id: props.taskId,
    // TODO: переделать под Enum
    commentable_type: 'tm_tasks'
  }).finally(() => {
    isCommentsLoading.value = false
  })
}

// TODO: Надо все таки из стора вызывать метод т.к. в task надо добавить commentsIds
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

onMounted(() => {
  loadComments()
})
</script>

<style scoped lang="scss">

</style>
