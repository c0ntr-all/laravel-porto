<template>
  <div class="post-form-files-upload">
    <q-file
      v-model="attachmentModel"
      label="Выберите файлы"
      outlined
      use-chips
      multiple
      clearable
      dense
      :accept="POST_FILE_ACCEPT"
      @update:model-value="handleFilesSelected"
    >
      <template #prepend>
        <q-icon name="attach_file" />
      </template>
    </q-file>

    <div class="text-caption text-grey-7 q-mt-xs">
      Фото, видео, PDF, Office, архивы
    </div>

    <q-banner
      v-if="unsupportedFiles.length"
      class="bg-orange-1 text-orange-10 q-mt-sm"
      rounded
      dense
    >
      Неподдерживаемые файлы: {{ unsupportedNames }}
    </q-banner>

    <div v-if="attachmentModel?.length" class="post-form-files-upload__grid q-mt-sm">
      <PostFormAttachmentPreview
        v-for="file in attachmentModel"
        :key="`${file.name}-${file.size}-${file.lastModified}`"
        :file="file"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useQuasar } from 'quasar'
import { POST_FILE_ACCEPT } from 'src/constants/LifeLog/attachment'
import { getUnsupportedFiles, groupFileTypes } from 'src/services/post.service'
import PostFormAttachmentPreview from 'src/components/client/LifeLog/forms/PostFormAttachmentPreview.vue'

const attachmentModel = defineModel<File[]>()
const $q = useQuasar()

const unsupportedFiles = computed(() =>
  getUnsupportedFiles(attachmentModel.value ?? [])
)

const unsupportedNames = computed(() =>
  unsupportedFiles.value.map(file => file.name).join(', ')
)

function handleFilesSelected(files: File[] | null) {
  if (!files?.length) {
    attachmentModel.value = []
    return
  }

  const groups = groupFileTypes(files)

  attachmentModel.value = [
    ...groups.images,
    ...groups.videos,
    ...groups.documents
  ]

  if (groups.other.length) {
    $q.notify({
      type: 'warning',
      message: `Некоторые файлы не поддерживаются: ${groups.other.map(file => file.name).join(', ')}`
    })
  }
}
</script>

<style lang="scss" scoped>
.post-form-files-upload {
  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
    gap: 8px;
  }
}
</style>
