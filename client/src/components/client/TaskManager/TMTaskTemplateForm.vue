<template>
  <div class="task-template-form">
    <q-input
      v-model="model.title"
      class="q-mb-md"
      label="Название шаблона"
      :rules="[val => !!val?.trim() || 'Обязательное поле']"
      dense
      outlined
    />

    <q-input
      v-model="model.content"
      class="q-mb-md"
      label="Описание"
      type="textarea"
      autogrow
      dense
      outlined
    />

    <div class="text-subtitle1 q-mb-sm">Чеклисты</div>

    <q-card
      v-for="(checklist, checklistIndex) in model.checklists"
      :key="checklistIndex"
      class="task-template-form__checklist q-mb-md"
      flat
      bordered
    >
      <q-card-section>
        <div class="row items-start q-col-gutter-sm">
          <div class="col">
            <q-input
              v-model="checklist.title"
              label="Название чеклиста"
              dense
              outlined
            />
          </div>
          <div class="col-auto">
            <q-btn
              v-if="model.checklists.length > 1"
              flat
              round
              dense
              icon="delete"
              color="negative"
              @click="removeChecklist(checklistIndex)"
            />
          </div>
        </div>

        <div class="q-mt-md">
          <div
            v-for="(item, itemIndex) in checklist.items"
            :key="itemIndex"
            class="row items-center q-col-gutter-sm q-mb-sm"
          >
            <div class="col">
              <q-input
                v-model="item.title"
                :label="`Пункт ${itemIndex + 1}`"
                dense
                outlined
              />
            </div>
            <div class="col-auto">
              <q-btn
                v-if="checklist.items.length > 1"
                flat
                round
                dense
                icon="remove_circle_outline"
                color="negative"
                @click="removeChecklistItem(checklistIndex, itemIndex)"
              />
            </div>
          </div>

          <q-btn
            flat
            dense
            no-caps
            color="primary"
            icon="add"
            label="Добавить пункт"
            @click="addChecklistItem(checklistIndex)"
          />
        </div>
      </q-card-section>
    </q-card>

    <q-btn
      outline
      no-caps
      color="primary"
      icon="add"
      label="Добавить чеклист"
      class="q-mb-md"
      @click="addChecklist"
    />

    <div class="row justify-end q-gutter-sm">
      <q-btn flat no-caps label="Отмена" @click="emit('cancel')" />
      <q-btn
        color="primary"
        no-caps
        :label="submitLabel"
        :loading="submitting"
        :disable="submitting"
        @click="submit"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import {
  createEmptyChecklist,
  createEmptyTaskTemplateForm,
  cloneTaskTemplateFormModel,
  mapTaskTemplateFormToPayload
} from 'src/api/mappers/task-template.mapper'
import { ITaskTemplateFormModel } from 'src/types'

const props = withDefaults(defineProps<{
  initialModel?: ITaskTemplateFormModel
  submitLabel?: string
  submitting?: boolean
}>(), {
  submitLabel: 'Сохранить',
  submitting: false
})

const emit = defineEmits<{
  submit: [payload: ReturnType<typeof mapTaskTemplateFormToPayload>]
  cancel: []
}>()

const model = ref<ITaskTemplateFormModel>(
  props.initialModel
    ? cloneTaskTemplateFormModel(props.initialModel)
    : createEmptyTaskTemplateForm()
)

const addChecklist = () => {
  model.value.checklists.push(createEmptyChecklist())
}

const removeChecklist = (index: number) => {
  model.value.checklists.splice(index, 1)
}

const addChecklistItem = (checklistIndex: number) => {
  model.value.checklists[checklistIndex].items.push({ title: '' })
}

const removeChecklistItem = (checklistIndex: number, itemIndex: number) => {
  model.value.checklists[checklistIndex].items.splice(itemIndex, 1)
}

const submit = () => {
  if (!model.value.title.trim()) return

  emit('submit', mapTaskTemplateFormToPayload(model.value))
}
</script>

<style lang="scss" scoped>
.task-template-form {
  &__checklist {
    background: #fafafa;
  }
}
</style>
