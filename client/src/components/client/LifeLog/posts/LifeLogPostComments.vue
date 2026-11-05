<template>
  <section
    :id="panelId"
    class="ll-post-comments"
  >
    <header class="ll-post-comments__header">
      <div class="ll-post-comments__title">
        Комментарии
        <span
          v-if="displayCount"
          class="ll-post-comments__count"
        >{{ displayCount }}</span>
      </div>
      <q-btn
        flat
        round
        dense
        color="grey-6"
        icon="close"
        aria-label="Закрыть комментарии"
        @click="emit('close')"
      />
    </header>

    <div
      v-if="isLoading"
      class="ll-post-comments__state"
    >
      <q-spinner color="primary" size="22px" />
    </div>

    <div
      v-else-if="loadFailed"
      class="ll-post-comments__state"
    >
      <span>Не удалось загрузить комментарии</span>
      <q-btn
        flat
        dense
        no-caps
        color="primary"
        label="Повторить"
        @click="loadComments"
      />
    </div>

    <div
      v-else
      ref="listRef"
      class="ll-post-comments__list"
    >
      <p
        v-if="!comments.length"
        class="ll-post-comments__empty"
      >
        Пока нет комментариев. Напишите первый.
      </p>

      <article
        v-for="comment in comments"
        :key="comment.id"
        class="ll-post-comments__item"
      >
        <AppUserAvatar
          :user="authorUser(comment.user)"
          size="32px"
        />

        <div class="ll-post-comments__bubble">
          <div class="ll-post-comments__meta">
            <span class="ll-post-comments__author">{{ authorName(comment.user) }}</span>
            <time class="ll-post-comments__time">{{ formatTime(comment.created_at) }}</time>
            <span
              v-if="isEdited(comment)"
              class="ll-post-comments__edited"
            >изменено</span>

            <q-btn
              v-if="isOwn(comment) && editingId !== comment.id"
              class="ll-post-comments__menu"
              flat
              round
              dense
              size="sm"
              color="grey-6"
              icon="more_horiz"
              aria-label="Действия с комментарием"
            >
              <q-menu
                auto-close
                anchor="bottom right"
                self="top right"
              >
                <q-list dense style="min-width: 148px">
                  <q-item
                    clickable
                    @click="startEdit(comment)"
                  >
                    <q-item-section avatar>
                      <q-icon name="edit" size="xs" />
                    </q-item-section>
                    <q-item-section>Изменить</q-item-section>
                  </q-item>
                  <q-item
                    clickable
                    @click="confirmDelete(comment)"
                  >
                    <q-item-section avatar>
                      <q-icon name="delete_outline" size="xs" color="negative" />
                    </q-item-section>
                    <q-item-section class="text-negative">Удалить</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
          </div>

          <q-input
            v-if="editingId === comment.id"
            v-model="editDraft"
            class="ll-post-comments__edit"
            type="textarea"
            autogrow
            outlined
            dense
            maxlength="1000"
            :disable="savingId === comment.id"
            @keydown.enter.exact.prevent="saveEdit(comment)"
            @keydown.esc.prevent="cancelEdit"
          />
          <p
            v-else
            class="ll-post-comments__content"
          >{{ comment.content }}</p>

          <div
            v-if="editingId === comment.id"
            class="ll-post-comments__edit-actions"
          >
            <q-btn
              flat
              dense
              no-caps
              color="grey-7"
              label="Отмена"
              :disable="savingId === comment.id"
              @click="cancelEdit"
            />
            <q-btn
              unelevated
              dense
              no-caps
              color="primary"
              label="Сохранить"
              :loading="savingId === comment.id"
              :disable="!editDraft.trim()"
              @click="saveEdit(comment)"
            />
          </div>
        </div>
      </article>
    </div>

    <form
      class="ll-post-comments__form"
      @submit.prevent="submit"
    >
      <AppUserAvatar
        :user="userStore.user"
        size="32px"
      />
      <q-input
        v-model="draft"
        class="ll-post-comments__input col"
        type="textarea"
        autogrow
        outlined
        dense
        autofocus
        maxlength="1000"
        placeholder="Написать комментарий…"
        :disable="isSending"
        @keydown.enter.exact.prevent="submit"
      />
      <q-btn
        class="ll-post-comments__send"
        type="submit"
        round
        unelevated
        color="primary"
        icon="send"
        aria-label="Отправить комментарий"
        :disable="!draft.trim()"
        :loading="isSending"
      >
        <q-tooltip>Отправить</q-tooltip>
      </q-btn>
    </form>
    <p class="ll-post-comments__hint">Enter — отправить, Shift+Enter — новая строка</p>
  </section>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import { Dialog } from 'quasar'
import { commentApi } from 'src/api/requests/commentApi'
import { mapCommentResponse, mapCommentsResponse } from 'src/api/mappers/comment.mapper'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'
import { UserRoleEnum } from 'src/enums/User/UserRoleEnum'
import { usePostStore } from 'src/stores/modules/postStore'
import { useUserStore } from 'src/stores/modules/userStore'
import { IPost } from 'src/types'
import { IGalleryComment, IGalleryCommentAuthor } from 'src/types/gallery'
import { IUser } from 'src/types/user'
import { humanDatetime } from 'src/utils/datetime'
import { handleApiError } from 'src/utils/jsonapi'

const POST_COMMENTABLE_TYPE = 'll_posts'

const props = defineProps<{
  post: IPost
}>()

const emit = defineEmits<{
  close: []
}>()

const postStore = usePostStore()
const userStore = useUserStore()

const comments = ref<IGalleryComment[]>([])
const draft = ref('')
const editDraft = ref('')
const editingId = ref<string | null>(null)
const savingId = ref<string | null>(null)
const isLoading = ref(false)
const loadFailed = ref(false)
const isSending = ref(false)
const listRef = ref<HTMLElement | null>(null)

const panelId = computed(() => `ll-post-comments-${props.post.id}`)
const displayCount = computed(() => {
  if (isLoading.value || loadFailed.value) {
    return props.post.comments_count ?? 0
  }

  return comments.value.length
})

function authorUser(author: IGalleryCommentAuthor | null): IUser {
  return {
    id: author?.id ?? '',
    name: author?.name || 'Пользователь',
    email: author?.email ?? '',
    role: UserRoleEnum.Default,
    roles: [],
    avatar: author?.avatar ?? null
  }
}

function authorName(author: IGalleryCommentAuthor | null): string {
  return author?.name || 'Пользователь'
}

function formatTime(value: string): string {
  return value ? humanDatetime(value) : ''
}

function isEdited(comment: IGalleryComment): boolean {
  if (!comment.updated_at || !comment.created_at) {
    return false
  }

  return comment.updated_at.slice(0, 19) !== comment.created_at.slice(0, 19)
}

function isOwn(comment: IGalleryComment): boolean {
  const userId = String(userStore.user.id || '')
  const authorId = String(comment.user?.id || '')

  return userId !== '' && userId === authorId
}

function syncCount(): void {
  postStore.setCommentsCount(props.post.id, comments.value.length)
}

async function scrollToEnd(): Promise<void> {
  await nextTick()
  const list = listRef.value
  if (!list) {
    return
  }

  list.scrollTop = list.scrollHeight
}

async function loadComments(): Promise<void> {
  isLoading.value = true
  loadFailed.value = false

  try {
    const response = await commentApi.getComments({
      commentable_id: props.post.id,
      commentable_type: POST_COMMENTABLE_TYPE
    }, 'created_at')
    comments.value = mapCommentsResponse(response).sort((left, right) =>
      left.created_at.localeCompare(right.created_at)
    )
    syncCount()
    await scrollToEnd()
  } catch (error) {
    handleApiError(error)
    loadFailed.value = true
    comments.value = []
  } finally {
    isLoading.value = false
  }
}

async function submit(): Promise<void> {
  const content = draft.value.trim()

  if (!content || isSending.value || props.post.is_pending) {
    return
  }

  isSending.value = true

  try {
    const response = await commentApi.createComment({
      commentable_id: props.post.id,
      commentable_type: POST_COMMENTABLE_TYPE,
      content
    })
    comments.value = [...comments.value, mapCommentResponse(response)]
    draft.value = ''
    syncCount()
    await scrollToEnd()
  } catch (error) {
    handleApiError(error)
  } finally {
    isSending.value = false
  }
}

function startEdit(comment: IGalleryComment): void {
  editingId.value = comment.id
  editDraft.value = comment.content
}

function cancelEdit(): void {
  editingId.value = null
  editDraft.value = ''
}

async function saveEdit(comment: IGalleryComment): Promise<void> {
  const content = editDraft.value.trim()

  if (!content || savingId.value) {
    return
  }

  if (content === comment.content) {
    cancelEdit()
    return
  }

  savingId.value = comment.id

  try {
    const response = await commentApi.updateComment(comment.id, content)
    const updated = mapCommentResponse(response)
    comments.value = comments.value.map(item => item.id === updated.id ? updated : item)
    cancelEdit()
  } catch (error) {
    handleApiError(error)
  } finally {
    savingId.value = null
  }
}

function confirmDelete(comment: IGalleryComment): void {
  Dialog.create({
    title: 'Удалить комментарий?',
    message: 'Комментарий исчезнет из обсуждения.',
    cancel: { label: 'Отмена', flat: true, noCaps: true },
    ok: { label: 'Удалить', color: 'negative', unelevated: true, noCaps: true },
    persistent: true
  }).onOk(() => {
    void removeComment(comment)
  })
}

async function removeComment(comment: IGalleryComment): Promise<void> {
  try {
    await commentApi.deleteComment(comment.id)
    comments.value = comments.value.filter(item => item.id !== comment.id)
    if (editingId.value === comment.id) {
      cancelEdit()
    }
    syncCount()
  } catch (error) {
    handleApiError(error)
  }
}

onMounted(() => {
  void loadComments()
})
</script>

<style scoped lang="scss">
.ll-post-comments {
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid #eef2f6;
  animation: ll-comments-in 0.16s ease;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
  }

  &__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
  }

  &__count {
    min-width: 18px;
    padding: 0 6px;
    border-radius: 999px;
    background: #eef2ff;
    color: var(--q-primary);
    font-size: 11px;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
  }

  &__state,
  &__empty {
    margin: 0 0 12px;
    color: #94a3b8;
    font-size: 13px;
    line-height: 1.4;
  }

  &__state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 48px;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 320px;
    overflow: auto;
    padding-right: 2px;
  }

  &__item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
  }

  &__bubble {
    min-width: 0;
    flex: 1;
    padding: 8px 10px 8px 12px;
    border-radius: 4px 14px 14px 14px;
    background: #f8fafc;
  }

  &__meta {
    display: flex;
    align-items: center;
    gap: 6px;
    min-height: 24px;
  }

  &__author {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
  }

  &__time,
  &__edited {
    font-size: 11px;
    color: #94a3b8;
    white-space: nowrap;
  }

  &__menu {
    margin-left: auto;
  }

  &__content {
    margin: 2px 0 0;
    color: #334155;
    font-size: 14px;
    line-height: 1.45;
    white-space: pre-wrap;
    word-break: break-word;
  }

  &__edit {
    margin-top: 6px;
  }

  &__edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    margin-top: 6px;
  }

  &__form {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    margin-top: 12px;
  }

  &__input {
    flex: 1;
    min-width: 0;
  }

  &__send {
    flex-shrink: 0;
    margin-bottom: 2px;
  }

  &__hint {
    margin: 6px 0 0 40px;
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.3;
  }
}

@keyframes ll-comments-in {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }

  to {
    opacity: 1;
    transform: none;
  }
}
</style>
