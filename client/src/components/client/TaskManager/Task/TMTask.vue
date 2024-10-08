<template>
  <div class="text task-item">
    <a
      href="#"
      class="task-item__link"
      @click.prevent="showModal = true"
    >{{ task.title }}</a>
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
                <span>{{ comment.user_name }}</span>
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
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import { handleApiError } from 'src/utils/jsonapi'

interface Comment {
  id: string
  user_name: string
  content: string
  created_at: string
}

interface Task {
  id: string
  title: string
  content?: string
  completed: boolean
  relationships: {
    comments: {
      data: Comment[],
      meta: {
        count: number
      }
    }
  }
}

interface ApiResponse {
  comments: Comment[]
}

interface TaskAttributes {
  title: string
  completed: boolean
  content?: string
  created_at?: string
  comments?: Comment[]
}

interface UpdateTaskResponse {
  data: {
    type: string
    id: string
    attributes: TaskAttributes
  },
  meta: {
    message?: string
  }
}

const props = defineProps<{ task: Task }>()
const $q = useQuasar()

const showModal = ref(false)
const titlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const contentPopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const task = ref<Task>(props.task)
const comment = ref('')

const updateTitle = (value: string) => {
  api.patch<UpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    title: value
  }).then(response => {
    $q.notify({
      type: 'positive',
      message: response?.data.meta.message || 'Task title successfully updated!'
    })
    titlePopup.value?.set()
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
    titlePopup.value?.cancel()
  })
}

const updateContent = (value: string) => {
  api.patch<UpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    content: value
  }).then(response => {
    $q.notify({
      type: 'positive',
      message: response?.data.meta.message || 'Task content successfully updated!'
    })
    contentPopup.value?.set()
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
    contentPopup.value?.cancel()
  })
}

const createComment = () => {
  api.post<ApiResponse>('v1/comments/store', {
    commentable_id: task.value.id,
    commentable_type: 'task',
    content: comment.value
  }).then((response) => {
    task.value.relationships.comments.data = task.value.relationships.comments.data || []
    task.value.relationships.comments.data.push(response.data.comments[0])

    $q.notify({
      type: 'positive',
      message: 'Комментарий успешно добавлен!'
    })
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}
</script>

<style lang="scss">
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
        background-color: #f4f5f7;
        border-bottom-color: #091e4240;
      }
    }
  }
}

.text {
  font-size: 14px;
}
</style>
