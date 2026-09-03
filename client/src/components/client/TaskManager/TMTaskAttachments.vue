<template>
  <section class="task-attachments">
    <div class="task-attachments__head">
      <div class="task-attachments__title">
        Вложения
        <span
          v-if="attachments.length"
          class="task-attachments__count"
        >
          {{ attachments.length }}
        </span>
      </div>
      <q-btn
        icon="attach_file"
        label="Добавить"
        color="grey-8"
        outline
        dense
        no-caps
        :loading="isUploading"
        @click="openFilePicker"
      />
    </div>

    <input
      ref="fileInputRef"
      class="hidden"
      type="file"
      multiple
      :accept="TASK_FILE_ACCEPT"
      @change="handleFileInput"
    >

    <div
      class="task-attachments__dropzone"
      :class="{
        'task-attachments__dropzone--active': isDragging,
        'task-attachments__dropzone--empty': !attachments.length
      }"
      @dragenter.prevent="isDragging = true"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="!attachments.length && openFilePicker()"
    >
      <template v-if="!attachments.length && !isUploading">
        <q-icon name="cloud_upload" size="28px" color="grey-5" />
        <div>Перетащите файлы сюда или нажмите, чтобы выбрать</div>
        <div class="task-attachments__hint">
          Фото, видео MP4, PDF, Office и архивы · до 20 МБ
        </div>
      </template>

      <div
        v-else
        class="task-attachments__list"
      >
        <div
          v-if="mediaAttachments.length"
          class="task-attachments__media"
        >
          <TMTaskAttachmentItem
            v-for="attachment in mediaAttachments"
            :key="attachment.id"
            :attachment="attachment"
            @preview="openPreview(attachment.id)"
            @remove="confirmRemove(attachment)"
          />
        </div>

        <div
          v-if="documentAttachments.length"
          class="task-attachments__documents"
        >
          <TMTaskAttachmentItem
            v-for="attachment in documentAttachments"
            :key="attachment.id"
            :attachment="attachment"
            @remove="confirmRemove(attachment)"
          />
        </div>
      </div>
    </div>

    <q-linear-progress
      v-if="isUploading"
      :value="uploadProgress / 100"
      color="primary"
      class="q-mt-sm"
      size="4px"
    />

    <q-dialog v-model="showPreview">
      <q-card class="task-attachments__preview">
        <q-btn
          class="task-attachments__preview-close"
          icon="close"
          round
          flat
          dense
          v-close-popup
        />
        <AppVideo
          v-if="previewAttachment && isGalleryVideo(previewAttachment)"
          :src="resolveMediaUrl(previewAttachment.original_path)"
          :autoplay="true"
        />
        <q-img
          v-else-if="previewAttachment"
          :src="resolveMediaUrl(previewAttachment.preview_thumb_path || previewAttachment.original_path)"
          fit="contain"
        />
      </q-card>
    </q-dialog>
  </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Dialog, useQuasar } from 'quasar'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { IPostAttachment, IPostDocumentAttachment, IPostGalleryAttachment } from 'src/types'
import { getPostAttachmentDeleteId, splitPostAttachments } from 'src/utils/attachment'
import { isGalleryVideo, resolveMediaUrl } from 'src/utils/gallery'
import { splitTaskFiles } from 'src/utils/TaskManager/attachment'
import { TASK_FILE_ACCEPT } from 'src/constants/TaskManager/attachment'
import TMTaskAttachmentItem from 'src/components/client/TaskManager/TMTaskAttachmentItem.vue'
import AppVideo from 'src/components/default/AppVideo.vue'

const props = defineProps<{
  taskId: string
}>()

const taskStore = useTaskStore()
const $q = useQuasar()

const fileInputRef = ref<HTMLInputElement | null>(null)
const isDragging = ref(false)
const isUploading = ref(false)
const uploadProgress = ref(0)
const showPreview = ref(false)
const previewId = ref('')

const attachments = computed<IPostAttachment[]>(() => {
  const ids = taskStore.tasks.byId[props.taskId]?.attachmentsIds ?? []
  return ids
    .map(id => taskStore.attachments.byId[id])
    .filter((item): item is IPostAttachment => Boolean(item))
})

const splitAttachments = computed(() => splitPostAttachments(attachments.value))
const mediaAttachments = computed(() => splitAttachments.value.media as IPostGalleryAttachment[])
const documentAttachments = computed(() => splitAttachments.value.documents as IPostDocumentAttachment[])
const previewAttachment = computed(() =>
  mediaAttachments.value.find(item => item.id === previewId.value)
)

function openFilePicker() {
  fileInputRef.value?.click()
}

defineExpose({
  openFilePicker
})

function handleFileInput(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files ?? [])
  input.value = ''
  void uploadFiles(files)
}

function handleDrop(event: DragEvent) {
  isDragging.value = false
  const files = Array.from(event.dataTransfer?.files ?? [])
  void uploadFiles(files)
}

async function uploadFiles(files: File[]) {
  if (!files.length || isUploading.value) return

  const { supported, unsupported, tooLarge } = splitTaskFiles(files)

  if (unsupported.length) {
    $q.notify({
      type: 'warning',
      message: `Неподдерживаемые файлы: ${unsupported.map(file => file.name).join(', ')}`
    })
  }

  if (tooLarge.length) {
    $q.notify({
      type: 'warning',
      message: `Слишком большие файлы (макс. 20 МБ): ${tooLarge.map(file => file.name).join(', ')}`
    })
  }

  if (!supported.length) return

  isUploading.value = true
  uploadProgress.value = 0
  try {
    await taskStore.uploadTaskAttachments(props.taskId, supported, percent => {
      uploadProgress.value = percent
    })
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
  }
}

function openPreview(id: string) {
  previewId.value = id
  showPreview.value = true
}

function confirmRemove(attachment: IPostAttachment) {
  Dialog.create({
    title: 'Открепить файл?',
    message: 'Файл будет откреплён от задачи.',
    cancel: { label: 'Отмена', flat: true },
    ok: { label: 'Открепить', color: 'negative' },
    persistent: true
  }).onOk(() => {
    void taskStore.deleteTaskAttachment(props.taskId, getPostAttachmentDeleteId(attachment))
  })
}
</script>

<style scoped lang="scss">
.task-attachments {
  &__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
  }

  &__title {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  &__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #e4e6ee;
    color: #4b5563;
    font-size: 11px;
    letter-spacing: 0;
  }

  &__dropzone {
    min-height: 52px;
    padding: 10px;
    border: 1px dashed #d5d8e4;
    border-radius: 10px;
    color: #6b7280;
    font-size: 13px;
    text-align: center;
    transition: border-color 0.15s ease, background-color 0.15s ease;

    &--empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      min-height: 120px;
      cursor: pointer;

      &:hover {
        background: #f7f8fb;
      }
    }

    &--active {
      background: #f4f1ff;
      border-color: $primary;
    }
  }

  &__hint {
    color: #9ca3af;
    font-size: 12px;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: left;
  }

  &__media {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(104px, 1fr));
    gap: 8px;
  }

  &__documents {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__preview {
    position: relative;
    width: min(860px, 92vw);
    max-height: 90vh;
    overflow: hidden;
    background: #111827;
  }

  &__preview-close {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 1;
    color: #fff;
  }
}

.hidden {
  display: none;
}
</style>
