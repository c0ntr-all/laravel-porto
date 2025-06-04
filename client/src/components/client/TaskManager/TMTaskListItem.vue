<template>
  <div class="text task-item">
    <div
      class="task-item__link"
      :class="{
        'task-item__link--finished': isFinished,
        'task-item__link--declined': isDeclined
      }"
      @click.prevent="openTask"
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
              @click="switchTaskFinishing(true)"
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
              @click="switchTaskFinishing(false)"
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
              v-if="!isDeclined"
              @click="switchTaskDeclanation(true)"
              clickable
            >
              <q-item-section>
                <div class="flex items-center">
                  <q-icon
                    size="xs"
                    name="block"
                    flat
                    round
                    dense
                  />
                  <div class="q-ml-xs">Decline</div>
                </div>
              </q-item-section>
            </q-item>
            <q-item
              v-else
              @click="switchTaskDeclanation(false)"
              clickable
            >
              <q-item-section>
                <div class="flex items-center">
                  <q-icon
                    size="xs"
                    name="hide_source"
                    flat
                    round
                    dense
                  />
                  <div class="q-ml-xs">Undecline</div>
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
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { AxiosError } from 'axios'
import {
  ITask,
  IDeclineTaskResponse,
  IDeleteTaskResponse,
  IUpdateTaskResponse
} from 'src/components/client/TaskManager/types'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'

const props = defineProps<{
  task: ITask
}>()
const emit = defineEmits<{
  (e: 'opened', task: ITask): void
}>()
const task = ref(props.task)
const isFinished = computed(() => {
  return task.value.finished_at !== null
})
const isDeclined = computed(() => {
  return task.value.is_declined === true
})

const openTask = () => {
  emit('opened', task.value)
}

const switchTaskFinishing = (status: boolean) => {
  api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    is_finished: status
  }).then(response => {
    task.value.finished_at = response.data.data.attributes.finished_at
    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const switchTaskDeclanation = (status: boolean) => {
  api.patch<IDeclineTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    is_declined: status
  }).then(response => {
    task.value.is_declined = response.data.data.attributes.is_declined
    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const deleteTask = () => {
  api.delete<IDeleteTaskResponse>(`v1/task-manager/tasks/${task.value.id}`)
    .then(response => {
      handleApiSuccess(response)
    }).catch((error: AxiosError<{ message: string }>) => {
      handleApiError(error)
    })
}
</script>

<style scoped lang="scss">
.task-item {
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

    &--declined {
      background-color: #ebbfbf;

      &:hover {
        background-color: #cfa7a7;
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

.text {
  font-size: 14px;
}
</style>
