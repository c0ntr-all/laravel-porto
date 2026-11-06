<template>
  <div class="viewer-comments">
    <div class="viewer-comments__list">
      <div v-if="isLoading" class="viewer-comments__state">
        <q-spinner color="primary" size="24px" />
      </div>
      <div v-else-if="comments.length" class="column q-gutter-sm">
        <div
          v-for="comment in comments"
          :key="comment.id"
          class="viewer-comments__item"
        >
          <div class="viewer-comments__meta">
            <span class="viewer-comments__author">{{ comment.user?.name || 'User' }}</span>
            <time class="viewer-comments__time">{{ formatTime(comment.created_at) }}</time>
          </div>
          <div class="viewer-comments__content">{{ comment.content }}</div>
        </div>
      </div>
      <p v-else class="viewer-comments__state text-grey-6">Комментариев пока нет</p>
    </div>

    <div class="viewer-comments__form">
      <q-input
        v-model="draft"
        placeholder="Написать комментарий..."
        outlined
        dense
        autogrow
        maxlength="1000"
        counter
        :disable="isSending || !commentableId"
        @keydown.enter.exact.prevent="submit"
      />
      <q-btn
        class="q-mt-sm"
        color="primary"
        unelevated
        no-caps
        label="Отправить"
        :disable="!draft.trim() || !commentableId"
        :loading="isSending"
        @click="submit"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useCommentStore } from 'src/stores/modules/commentStore'
import { humanDatetime } from 'src/utils/datetime'

const props = defineProps<{
  commentableId?: string
  commentableType?: string
}>()

const commentStore = useCommentStore()
const draft = ref('')

const cacheKey = computed(() => {
  if (!props.commentableId || !props.commentableType) {
    return ''
  }

  return `${props.commentableType}:${props.commentableId}`
})
const comments = computed(() => {
  if (!cacheKey.value) {
    return []
  }

  return commentStore.commentsByKey[cacheKey.value] ?? []
})
const isLoading = computed(() => {
  if (!cacheKey.value) {
    return false
  }

  return Boolean(commentStore.loadingByKey[cacheKey.value])
})
const isSending = computed(() => {
  if (!cacheKey.value) {
    return false
  }

  return Boolean(commentStore.sendingByKey[cacheKey.value])
})

function formatTime(value: string): string {
  return humanDatetime(value)
}

async function loadComments(): Promise<void> {
  if (!props.commentableId || !props.commentableType) {
    return
  }

  await commentStore.loadComments(props.commentableType, props.commentableId)
}

async function submit(): Promise<void> {
  const content = draft.value.trim()

  if (!content || !props.commentableId || !props.commentableType || isSending.value) {
    return
  }

  const created = await commentStore.createComment(
    props.commentableType,
    props.commentableId,
    content,
    { placement: 'start', notifySuccess: true }
  )

  if (created) {
    draft.value = ''
  }
}

watch(
  () => [props.commentableId, props.commentableType],
  () => {
    draft.value = ''
    void loadComments()
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.viewer-comments {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 0;

  &__list {
    flex: 1;
    min-height: 0;
    overflow: auto;
    padding-right: 4px;
  }

  &__item {
    padding: 10px 12px;
    border-radius: 12px;
    background: #f6f6fa;
  }

  &__meta {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
  }

  &__author {
    font-size: 13px;
    font-weight: 600;
    color: #282f53;
  }

  &__time {
    font-size: 11px;
    color: #9aa0b8;
    white-space: nowrap;
  }

  &__content {
    font-size: 13px;
    line-height: 1.4;
    color: #3d4158;
    white-space: pre-wrap;
    word-break: break-word;
  }

  &__state {
    display: flex;
    justify-content: center;
    padding: 24px 8px;
    font-size: 13px;
  }

  &__form {
    padding-top: 12px;
    margin-top: 12px;
    border-top: 1px solid #ececf4;
  }
}
</style>
