<template>
  <q-dialog
    :model-value="modelValue"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <q-card class="task-create-dialog">
      <q-card-section class="task-create-dialog__header">
        <div class="text-h6">Новая задача</div>
        <q-btn
          icon="close"
          flat
          round
          dense
          v-close-popup
        />
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input
          v-model="title"
          ref="titleRef"
          label="Название"
          :hint="selectedTemplateId ? 'Можно оставить пустым — возьмётся из шаблона' : undefined"
          :rules="titleRules"
          outlined
          dense
          autofocus
          maxlength="70"
          counter
          @keyup.enter="submit"
        />

        <q-select
          v-model="selectedTemplateId"
          :options="templateOptions"
          label="Шаблон"
          outlined
          dense
          emit-value
          map-options
          clearable
          options-dense
          :loading="templateStore.isLoading"
          :disable="isSubmitting"
        >
          <template #option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section>
                <q-item-label>{{ scope.opt.label }}</q-item-label>
                <q-item-label
                  v-if="scope.opt.caption"
                  caption
                >
                  {{ scope.opt.caption }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </template>
        </q-select>

        <q-select
          v-model="selectedListId"
          :options="listOptions"
          label="Список"
          outlined
          dense
          emit-value
          map-options
          clearable
          options-dense
          :disable="isSubmitting"
        />
      </q-card-section>

      <q-card-actions align="right" class="q-px-md q-pb-md">
        <q-btn
          label="Отмена"
          flat
          no-caps
          :disable="isSubmitting"
          v-close-popup
        />
        <q-btn
          label="Создать"
          color="primary"
          unelevated
          no-caps
          :loading="isSubmitting"
          :disable="!canSubmit"
          @click="submit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { useTaskTemplateStore } from 'src/stores/modules/taskTemplateStore'
import { ITask, ITaskCreatePayload } from 'src/types/TaskManager/task'

interface ITitleRef {
  hasError: boolean
  validate: () => boolean | Promise<boolean>
  resetValidation: () => void
}

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'created', task: ITask): void
}>()

const taskStore = useTaskStore()
const templateStore = useTaskTemplateStore()

const title = ref('')
const selectedTemplateId = ref<string | null>(null)
const selectedListId = ref<string | null>(null)
const isSubmitting = ref(false)
const titleRef = ref<ITitleRef | null>(null)

const templateOptions = computed(() =>
  templateStore.templates.map(template => ({
    label: template.title,
    value: template.id,
    caption: template.checklists.length
      ? `Чек-листов: ${template.checklists.length}`
      : undefined
  }))
)

const listOptions = computed(() =>
  taskStore.taskLists.allIds.map(id => ({
    label: taskStore.taskLists.byId[id]?.title || 'Без названия',
    value: id
  }))
)

const selectedTemplate = computed(() =>
  templateStore.templates.find(item => item.id === selectedTemplateId.value) || null
)

const canSubmit = computed(() => {
  if (isSubmitting.value) return false
  if (selectedTemplateId.value) return true
  return Boolean(title.value.trim())
})

const titleRules = computed(() => {
  if (selectedTemplateId.value) return []

  return [
    (val: string) => !!val?.trim() || 'Укажите название задачи'
  ]
})

watch(() => props.modelValue, async (open) => {
  if (!open) return

  resetForm()

  if (!templateStore.templates.length) {
    try {
      await templateStore.getTaskTemplates()
    } catch {
      // store already surfaces the error
    }
  }
})

watch(selectedTemplateId, (templateId) => {
  if (!templateId) return

  const template = selectedTemplate.value
  if (template && !title.value.trim()) {
    title.value = template.title
  }

  titleRef.value?.resetValidation()
})

function resetForm () {
  title.value = ''
  selectedTemplateId.value = null
  selectedListId.value = null
  isSubmitting.value = false
  titleRef.value?.resetValidation()
}

async function submit () {
  if (!canSubmit.value) return

  if (!selectedTemplateId.value) {
    const isValid = await titleRef.value?.validate()
    if (titleRef.value?.hasError || isValid === false) return
  }

  const payload: ITaskCreatePayload = {}
  const trimmedTitle = title.value.trim()

  if (trimmedTitle) {
    payload.title = trimmedTitle
  }

  if (selectedTemplateId.value) {
    payload.task_template_id = selectedTemplateId.value
  }

  if (selectedListId.value) {
    payload.task_list_id = selectedListId.value
  }

  if (!payload.title && !payload.task_template_id) return

  isSubmitting.value = true
  try {
    const created = await taskStore.createTask(payload)
    if (!created) return

    emit('created', created)
    emit('update:modelValue', false)
    resetForm()
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.task-create-dialog {
  width: 420px;
  max-width: 92vw;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
}
</style>
