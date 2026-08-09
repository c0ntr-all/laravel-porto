<template>
  <div class="task-templates-tab">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-subtitle1">
        Всего шаблонов: {{ templates.length }}
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Создать шаблон"
        no-caps
        @click="openCreateModal"
      />
    </div>

    <q-inner-loading :showing="isLoading">
      <q-spinner size="40px" color="primary" />
    </q-inner-loading>

    <q-table
      v-if="!isLoading"
      :rows="templates"
      :columns="columns"
      row-key="id"
      flat
      bordered
      :pagination="{ rowsPerPage: 10 }"
      no-data-label="Шаблоны не найдены"
    >
      <template #body-cell-checklists="props">
        <q-td :props="props">
          {{ props.row.checklists.length }}
        </q-td>
      </template>

      <template #body-cell-actions="props">
        <q-td :props="props" auto-width>
          <q-btn
            flat
            round
            dense
            icon="edit"
            color="primary"
            @click="openEditModal(props.row.id)"
          />
          <q-btn
            flat
            round
            dense
            icon="delete"
            color="negative"
            @click="confirmDelete(props.row)"
          />
        </q-td>
      </template>
    </q-table>

    <AppModal
      v-model="isModalOpen"
      width="760px"
      scrollable
    >
      <template #header>
        {{ modalTitle }}
      </template>
      <template #body>
        <TMTaskTemplateForm
          v-if="isModalOpen"
          :key="formKey"
          :initial-model="formModel"
          :submit-label="editingTemplateId ? 'Сохранить' : 'Создать'"
          :submitting="isSubmitting"
          @submit="onSubmit"
          @cancel="closeModal"
        />
      </template>
    </AppModal>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'
import AppModal from 'src/components/default/AppModal.vue'
import TMTaskTemplateForm from 'src/components/client/TaskManager/TMTaskTemplateForm.vue'
import { useTaskTemplateStore } from 'src/stores/modules/taskTemplateStore'
import {
  createEmptyTaskTemplateForm,
  mapTaskTemplateToForm
} from 'src/api/mappers/task-template.mapper'
import { handleApiError } from 'src/utils/jsonapi'
import { ITaskTemplate, ITaskTemplateFormModel, ITaskTemplatePayload } from 'src/types'

const $q = useQuasar()
const taskTemplateStore = useTaskTemplateStore()
const { templates, isLoading } = storeToRefs(taskTemplateStore)

const isModalOpen = ref(false)
const editingTemplateId = ref<string | null>(null)
const formModel = ref<ITaskTemplateFormModel>(createEmptyTaskTemplateForm())
const formKey = ref(0)
const isSubmitting = ref(false)

const columns = [
  {
    name: 'title',
    label: 'Название',
    align: 'left' as const,
    field: 'title',
    sortable: true
  },
  {
    name: 'content',
    label: 'Описание',
    align: 'left' as const,
    field: (row: ITaskTemplate) => row.content || '—',
    sortable: false
  },
  {
    name: 'checklists',
    label: 'Чеклисты',
    align: 'center' as const,
    field: 'checklists',
    sortable: false
  },
  {
    name: 'actions',
    label: 'Действия',
    align: 'center' as const,
    field: 'id',
    sortable: false
  }
]

const modalTitle = computed(() =>
  editingTemplateId.value ? 'Редактировать шаблон' : 'Создать шаблон'
)

const openCreateModal = () => {
  editingTemplateId.value = null
  formModel.value = createEmptyTaskTemplateForm()
  formKey.value += 1
  isModalOpen.value = true
}

const openEditModal = async (id: string) => {
  try {
    const template = await taskTemplateStore.getTaskTemplate(id)

    editingTemplateId.value = id
    formModel.value = mapTaskTemplateToForm(template)
    formKey.value += 1
    isModalOpen.value = true
  } catch (error) {
    handleApiError(error)
  }
}

const closeModal = () => {
  isModalOpen.value = false
  editingTemplateId.value = null
}

const onSubmit = async (payload: ITaskTemplatePayload) => {
  isSubmitting.value = true

  try {
    if (editingTemplateId.value) {
      await taskTemplateStore.updateTaskTemplate(editingTemplateId.value, payload)
    } else {
      await taskTemplateStore.createTaskTemplate(payload)
    }

    closeModal()
  } catch (error) {
    handleApiError(error)
  } finally {
    isSubmitting.value = false
  }
}

const confirmDelete = (template: ITaskTemplate) => {
  $q.dialog({
    title: 'Удалить шаблон?',
    message: `Шаблон «${template.title}» будет удалён без возможности восстановления.`,
    cancel: {
      label: 'Отмена',
      flat: true,
      noCaps: true
    },
    ok: {
      label: 'Удалить',
      color: 'negative',
      noCaps: true
    },
    persistent: true
  }).onOk(async () => {
    try {
      await taskTemplateStore.deleteTaskTemplate(template.id)
    } catch (error) {
      handleApiError(error)
    }
  })
}

onMounted(async () => {
  try {
    await taskTemplateStore.getTaskTemplates()
  } catch (error) {
    handleApiError(error)
  }
})
</script>
