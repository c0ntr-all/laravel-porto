<template>
  <div class="text task-item">
    <div
      class="task-item__link"
      :class="{'task-item__link--finished': isFinished}"
      @click.prevent="showModal = true"
    >
      {{ task.title }}
    </div>
    <div class="task-item__actions-button">
      <q-btn
        icon="more_horiz"
        label=""
        size="sm"
        dense
        flat
      >
        <q-menu cover auto-close anchor="bottom left" self="top start">
          <q-list dense>
            <q-item
              v-if="!isFinished"
              @click="finishTask"
              clickable
            >
              <q-item-section>
                <div class="flex items-center">
                  <q-icon
                    size="xs"
                    name="done"
                    flat
                    round
                    dense
                  />
                  <div class="q-ml-xs">Finish</div>
                </div>
              </q-item-section>
            </q-item>
            <q-item
              v-else
              @click="unFinishTask"
              clickable
            >
              <q-item-section>
                <div class="flex items-center">
                  <q-icon
                    size="xs"
                    name="remove_done"
                    flat
                    round
                    dense
                  />
                  <div class="q-ml-xs">Unfinish</div>
                </div>
              </q-item-section>
            </q-item>
            <q-item
              @click="deleteTask"
              clickable
            >
              <q-item-section>
                <div class="flex items-center">
                  <q-icon
                    size="xs"
                    name="delete"
                    flat
                    round
                    dense
                  />
                  <div class="q-ml-xs">Delete</div>
                </div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
    </div>
  </div>

  <q-dialog v-model="showModal">
    <q-card style="width: 768px; max-width: 80vw;">
      <q-card-section class="task__header">
        <div class="task__title text-h6">
          {{ task.title }}
          <q-popup-edit
            ref="titlePopup"
            v-model="task.title"
            auto-save
            v-slot="scope"
          >
            <q-input
              v-model="scope.value"
              @keyup.enter="updateTitle(scope.value)"
              dense
              autofocus
              counter
            />
            <div class="q-pt-sm">
              <q-btn @click="updateTitle(scope.value)" label="Save" color="primary" flat/>
              <q-btn @click="titlePopup?.cancel()" label="Cancel" color="primary" flat/>
            </div>
          </q-popup-edit>
        </div>
        <q-btn @click="showModal = false" class="task__close" icon="close" size="md" flat rounded dense/>
      </q-card-section>

      <q-separator dark/>

      <q-card-section>
        <div v-if="task.content" v-html="task.content" class="task__content"></div>
        <div v-else class="task__content text-grey-5">There is no description!</div>
        <q-popup-edit
          ref="contentPopup"
          v-model="task.content"
          v-slot="scope"
        >
          <q-editor
            v-model="scope.value"
            min-height="5rem"
            autofocus
            @keyup.enter.stop
          />
          <div class="q-pt-sm">
            <q-btn @click="updateContent(scope.value)" label="Save" color="primary" flat/>
            <q-btn @click="contentPopup?.cancel()" label="Cancel" color="primary" flat/>
          </div>
        </q-popup-edit>
      </q-card-section>

      <q-card-section>
        <div class="comments">
          <div class="text-h6 q-mb-sm">Comments</div>
          <div class="comments-form q-mb-md">
            <q-input
              v-model="comment"
              class="q-mb-sm"
              placeholder="Write a comment..."
              filled
              autogrow
            />
            <q-btn @click="createComment" color="primary" label="Send"/>
          </div>
          <div v-if="task?.relationships?.comments?.data" class="comments-list column q-gutter-sm">
            <q-card v-for="comment in task.relationships.comments.data" :key="comment.id">
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
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IComment, ITask, IUser } from 'src/components/client/TaskManager/types'
import { IIncludedItem } from 'src/components/client/Music/types'

interface ICreateCommentResponse {
  data: {
    type: string
    id: string
    attributes: {
      name: string
      content: string
      created_at: string
    }
    relationships: {
      user: {
        data: IUser
      }
    }
  },
  included: IIncludedItem[]
  meta: {
    message?: string
  }
}

interface IUpdateTaskResponse {
  data: {
    type: string
    id: string
    attributes: {
      title: string
      content?: string
      finished_at: string
      created_at?: string
      comments?: IComment[]
    }
  },
  meta: {
    message?: string
  }
}

interface IDeleteTaskResponse {
  meta: {
    message?: string
  }
}

const props = defineProps<{ task: ITask }>()
const showModal = ref(false)
const titlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const contentPopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const task = ref<ITask>(props.task)
const isFinished = computed(() => {
  return task.value.finished_at !== null
})
const comment = ref<string>('')

const updateTitle = (value: string) => {
  api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    title: value
  }).then(response => {
    handleApiSuccess(response)
    titlePopup.value?.set()
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
    titlePopup.value?.cancel()
  })
}

const updateContent = (value: string) => {
  api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    content: value
  }).then(response => {
    handleApiSuccess(response)
    contentPopup.value?.set()
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
    contentPopup.value?.cancel()
  })
}

const finishTask = () => {
  api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    is_finished: true
  }).then(response => {
    task.value.finished_at = response.data.data.attributes.finished_at
    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const unFinishTask = () => {
  api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    is_finished: false
  }).then(response => {
    task.value.finished_at = response.data.data.attributes.finished_at
    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const deleteTask = () => {
  api.delete<IDeleteTaskResponse>(`v1/task-manager/tasks/${task.value.id}`)
    .then(response => {
      handleApiSuccess(response)
      contentPopup.value?.set()
    }).catch((error: AxiosError<{ message: string }>) => {
      handleApiError(error)
      contentPopup.value?.cancel()
    })
}

const createComment = () => {
  api.post<ICreateCommentResponse>('v1/comments', {
    commentable_id: task.value.id,
    commentable_type: 'task',
    content: comment.value
  }).then((response) => {
    task.value.relationships.comments.data = task.value.relationships.comments.data || []
    const responseData = response.data.data

    const comment = {
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

    task.value.relationships.comments.data.push(comment)

    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}
</script>

<style lang="scss" scoped>
.task {
  &__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  &__title {
    width: 100%;

    &:hover {
      cursor: pointer;
      background: #f4f5f7;
    }
  }

  &__content {
    &:hover {
      cursor: pointer;
      background: #f4f5f7;
    }
  }

  &-item {
    position: relative;
    word-break: break-all;
    margin-bottom: 8px;

    &__link {
      display: block;
      padding: 8px;
      background-color: #fff;
      text-decoration: none;
      color: #000;
      border-radius: 3px;
      box-shadow: 0 1px 0 #091e4240;

      &:hover {
        cursor: pointer;
        background-color: #f4f5f7;
        border-bottom-color: #091e4240;
      }

      &--finished {
        background-color: #c9ebbf;

        &:hover {
          background-color: #b0cfa7;
        }
      }
    }
    &__actions-button {
      visibility: hidden;
      position: absolute;
      top: 50%;
      margin-top: -11px;
      right: 2px;
      z-index: 10;

      button {
        border-radius: 50%;
      }
    }

    &:hover {
      .task-item__actions-button {
        visibility: visible;
      }
    }

  }
}

.text {
  font-size: 14px;
}
</style>
