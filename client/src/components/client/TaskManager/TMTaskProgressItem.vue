<template>
  <q-expansion-item
    expand-icon-toggle
    expand-separator
    :icon="icon"
    :label="progressItem.title"
    :header-class="color"
    dense
  >
    <template #header>
      <div class="q-item__section column q-item__section--side justify-center q-item__section--avatar">
        <i
          v-if="isLast"
          class="q-icon notranslate material-icons"
          :class="{'q-icon--hover': isHovering}"
          aria-hidden="true"
          role="presentation"
          @mouseover="handleHover"
          @mouseleave="handleHoverLeave"
          @click="switchProgressItemFinality"
        >
          {{ icon }}
        </i>

        <!-- Версия без событий, если isLast === false -->
        <i
          v-else
          class="q-icon notranslate material-icons"
          aria-hidden="true"
          role="presentation"
        >
          {{ icon }}
        </i>
      </div>
      <div class="q-item__section column q-item__section--main justify-center">
        <div class="q-item__label">
          {{ progressItem.title }}
          <q-popup-edit
            ref="progressItemTitlePopup"
            v-model="progressItem.title"
            v-slot="scope"
            auto-save
          >
            <q-input
              v-model="scope.value"
              @keyup.enter="updateProgressItemTitle(scope.value)"
              dense
              autofocus
              counter
            />
            <div class="q-pt-sm">
              <q-btn @click="updateProgressItemTitle(scope.value)" label="Save" color="primary" flat/>
              <q-btn @click="progressItemTitlePopup?.cancel()" label="Cancel" color="primary" flat/>
            </div>
          </q-popup-edit>
        </div>
        <div class="q-item__label q-item__label--caption text-caption">
          {{ progressItem.finished_at }}
        </div>
      </div>
    </template>
    <q-card>
      <q-card-section>
        {{ progressItem.content }}
        <q-popup-edit
          ref="progressItemContentPopup"
          v-model="progressItem.content"
          v-slot="scope"
        >
          <q-editor
            v-model="scope.value"
            min-height="5rem"
            autofocus
            @keyup.enter.stop
          />
          <div class="q-pt-sm">
            <q-btn @click="updateProgressItemContent(scope.value)" label="Save" color="primary" flat/>
            <q-btn @click="progressItemContentPopup?.cancel()" label="Cancel" color="primary" flat/>
          </div>
        </q-popup-edit>
      </q-card-section>
    </q-card>
  </q-expansion-item>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IProgressItem } from 'src/components/client/TaskManager/types'

interface IUpdateChecklistResponse {
  data: {
    type: string
    id: string
    attributes: {
      task_id: string
      title: string
      content: string
      is_final: boolean
      created_at: string
      updated_at: string
    }
  },
  meta: {
    message?: string
  }
}

const props = defineProps<{
  progressItem: IProgressItem,
  isLast: boolean
}>()
const progressItem = ref(props.progressItem)
const colors = {
  default: 'text-black',
  final: 'text-green'
}
const icons = {
  default: 'star',
  final: 'flag'
}
const icon = ref(props.progressItem.is_final ? icons.final : icons.default)
const color = ref(props.progressItem.is_final ? colors.final : colors.default)
const isHovering = ref(false)
const progressItemTitlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const progressItemContentPopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)

const handleHover = () => {
  isHovering.value = true
  switchColor(!progressItem.value.is_final)
}

const handleHoverLeave = () => {
  isHovering.value = false
  switchColor(progressItem.value.is_final)
}

const switchColor = (isFinal: boolean) => {
  color.value = isFinal ? colors.final : colors.default
}

const switchIcon = (isFinal: boolean) => {
  icon.value = isFinal ? icons.final : icons.default
}

const switchStatus = (isFinal: boolean) => {
  switchColor(isFinal)
  switchIcon(isFinal)
}

const updateProgressItemTitle = async (newTitle: string) => {
  return await api.patch<IUpdateChecklistResponse>(
    `v1/task-manager/tasks/${progressItem.value.task_id}/progress/${progressItem.value.id}`,
    { title: newTitle })
    .then(response => {
      handleApiSuccess(response)

      const responseData = response.data.data
      progressItem.value.title = responseData.attributes.title
      progressItem.value.updated_at = responseData.attributes.updated_at

      if (progressItemTitlePopup?.value) {
        progressItemTitlePopup?.value.cancel()
      }
    })
    .catch((error: AxiosError<{ message: string }>) => {
      handleApiError(error)

      return Promise.reject(error)
    })
}

const updateProgressItemContent = async (newContent: string) => {
  return await api.patch<IUpdateChecklistResponse>(
    `v1/task-manager/tasks/${progressItem.value.task_id}/progress/${progressItem.value.id}`,
    { content: newContent })
    .then(response => {
      handleApiSuccess(response)

      const responseData = response.data.data
      progressItem.value.content = responseData.attributes.content
      progressItem.value.updated_at = responseData.attributes.updated_at

      if (progressItemContentPopup?.value) {
        progressItemContentPopup?.value.cancel()
      }
    })
    .catch((error: AxiosError<{ message: string }>) => {
      handleApiError(error)

      return Promise.reject(error)
    })
}

const switchProgressItemFinality = async () => {
  return await api.patch<IUpdateChecklistResponse>(
    `v1/task-manager/tasks/${progressItem.value.task_id}/progress/${progressItem.value.id}`,
    { is_final: !progressItem.value.is_final })
    .then(response => {
      handleApiSuccess(response)

      const responseData = response.data.data
      progressItem.value.is_final = responseData.attributes.is_final
      progressItem.value.updated_at = responseData.attributes.updated_at
    })
    .catch((error: AxiosError<{ message: string }>) => {
      handleApiError(error)

      return Promise.reject(error)
    })
}

watch(
  () => progressItem.value.is_final,
  (newValue) => {
    switchStatus(newValue)
  },
  { deep: false }
)
</script>

<style scoped lang="scss">
.q-icon {
  &.q-icon--hover {
    cursor: pointer;
  }
}
</style>
