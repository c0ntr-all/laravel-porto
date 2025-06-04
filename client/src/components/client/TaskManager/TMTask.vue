<template>
  <q-card class="task">
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
      <q-btn @click="closeTask" class="task__close" icon="close" size="md" flat rounded dense/>
    </q-card-section>

    <q-separator dark/>

    <div class="row">
      <div class="col-9">
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

        <template v-if="checklists?.length">
          <TMChecklist
            v-for="checklist in checklists"
            :key="checklist.id"
            :checklist="checklist"
            :task="task"
          />
        </template>

        <template v-if="progress?.length">
          <TMTaskProgress
            :progress="progress"
            :task="task"
          />
        </template>

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
      </div>
      <div class="col-3">
        <q-card-section class="column q-gutter-sm">
          <q-btn
            label="Add checklist"
            color="secondary"
            dense
            unelevated
          >
            <q-menu>
              <div class="row no-wrap q-pa-md">
                <div style="width: 250px">
                  <div class="text-h6 q-mb-md">Adding checklist</div>
                  <div class="flex column q-gutter-sm">
                    <q-input
                      v-model="newChecklistTitle"
                      label="Checklist name"
                      outlined
                      dense
                    />

                    <q-checkbox
                      v-model="isStrongChecklist"
                      label="Strong"
                    />

                    <q-btn
                      @click="createChecklist"
                      class="q-mb-xs"
                      label="Add"
                      color="primary"
                      unelevated
                    />
                  </div>
                </div>
              </div>
            </q-menu>
          </q-btn>
          <q-btn
            :disable="!isProgressAvailable"
            label="Add progress"
            color="secondary"
            dense
            unelevated
          >
            <q-menu ref="newProgressMenuRef">
              <div class="row no-wrap q-pa-md">
                <div style="width: 250px">
                  <div class="text-h6 q-mb-md">Adding progress</div>
                  <div class="flex column q-gutter-sm">
                    <q-checkbox
                      v-model="newProgressIsFinal"
                      label="Final"
                    />
                    <q-input
                      v-model="newProgressTitle"
                      type="text"
                      label="Progress title"
                      outlined
                      dense
                    />
                    <q-input
                      v-model="newProgressContent"
                      type="textarea"
                      label="Progress content"
                      outlined
                      dense
                    />
                    <q-input filled v-model="newProgressFinishedAt">
                      <template v-slot:prepend>
                        <q-icon name="event" class="cursor-pointer">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                            <q-date v-model="newProgressFinishedAt" mask="YYYY-MM-DD HH:mm">
                              <div class="row items-center justify-end">
                                <q-btn v-close-popup label="Close" color="primary" flat />
                              </div>
                            </q-date>
                          </q-popup-proxy>
                        </q-icon>
                      </template>

                      <template v-slot:append>
                        <q-icon name="access_time" class="cursor-pointer">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                            <q-time v-model="newProgressFinishedAt" mask="YYYY-MM-DD HH:mm" format24h>
                              <div class="row items-center justify-end">
                                <q-btn v-close-popup label="Close" color="primary" flat />
                              </div>
                            </q-time>
                          </q-popup-proxy>
                        </q-icon>
                      </template>
                    </q-input>

                    <q-btn
                      @click="createProgress"
                      class="q-mb-xs"
                      label="Add"
                      color="primary"
                      unelevated
                    />
                  </div>
                </div>
              </div>
            </q-menu>
          </q-btn>
        </q-card-section>
      </div>
    </div>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, provide, ref } from 'vue'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import {
  ICreateChecklistResponse, ICreateCommentResponse,
  ICreateProgressResponse,
  ITask,
  IUpdateTaskResponse
} from 'src/components/client/TaskManager/types'
import TMChecklist from 'src/components/client/TaskManager/TMChecklist.vue'
import TMTaskProgress from 'src/components/client/TaskManager/TMTaskProgress.vue'
import getCurrentDateTime from 'src/utils/datetime'

const props = defineProps<{ task: ITask }>()
const emit = defineEmits<{
  (e: 'closed'): void
}>()
const titlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const contentPopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const task = ref<ITask>(props.task)
const isProgressAvailable = computed(() => {
  return task.value?.relationships?.progress?.data.filter(item => item.is_final === true).length === 0
})
const comment = ref<string>('')
const newChecklistTitle = ref('Check list')
const isStrongChecklist = ref(false)
const newProgressMenuRef = ref()
const newProgressIsFinal = ref(false)
const newProgressContent = ref('')
const newProgressTitle = ref('')
const newProgressFinishedAt = ref(getCurrentDateTime())
const checklists = ref(task.value.relationships?.checklists?.data || [])
const progress = ref(task.value.relationships?.progress?.data || [])
const activeChecklistFormId = ref<string | null>(null)
provide('activeFormId', activeChecklistFormId)

// Only one form should be opened
const setActiveChecklistForm = (id: string | null) => {
  activeChecklistFormId.value = id
}
provide('setActiveChecklistForm', setActiveChecklistForm)

const closeTask = () => {
  emit('closed')
}

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

const createChecklist = () => {
  api.post<ICreateChecklistResponse>(`v1/task-manager/tasks/${task.value.id}/checklists`, {
    title: newChecklistTitle.value,
    is_strong: isStrongChecklist.value
  }).then((response) => {
    task.value.relationships.checklists = {
      data: task.value?.relationships?.checklists?.data || [],
      meta: {
        count: 1
      }
    }
    const responseData = response.data.data

    const checklist = {
      id: responseData.id,
      title: responseData.attributes.title,
      is_strong: responseData.attributes.is_strong,
      created_at: responseData.attributes.created_at,
      updated_at: responseData.attributes.updated_at,
      relationships: {
        checklistItems: {
          data: [],
          meta: {
            count: 1
          }
        }
      }
    }

    checklists.value.push(checklist)

    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const createProgress = () => {
  api.post<ICreateProgressResponse>(`v1/task-manager/tasks/${task.value.id}/progress`, {
    is_final: newProgressIsFinal.value,
    title: newProgressTitle.value,
    content: newProgressContent.value,
    finished_at: newProgressFinishedAt.value
  }).then((response) => {
    task.value.relationships.progress = {
      data: task.value?.relationships?.progress?.data || [],
      meta: {
        count: 1
      }
    }
    const responseData = response.data.data

    const newProgressItem = {
      id: responseData.id,
      ...responseData.attributes
    }

    progress.value.push(newProgressItem)

    clearProgressModel()

    if (newProgressItem.is_final) {
      newProgressMenuRef.value.hide()
    }

    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
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

    task.value.relationships.comments.data.push(newComment)

    clearCommentModel()

    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })

  const clearCommentModel = () => {
    comment.value = ''
  }
}

const clearProgressModel = () => {
  newProgressIsFinal.value = false
  newProgressContent.value = ''
  newProgressTitle.value = ''
  newProgressFinishedAt.value = ''
}
</script>

<style lang="scss" scoped>
.task {
  width: 768px;
  max-width: 80vw;

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
}
</style>
