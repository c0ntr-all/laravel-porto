<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card style="min-width: 420px">
      <q-card-section class="row items-center">
        <div class="text-h6">{{ definition?.name || 'Настройки виджета' }}</div>
        <q-space />
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input v-model="title" outlined dense label="Заголовок" />

        <template v-for="(field, key) in schema" :key="String(key)">
          <q-input
            v-if="field.type === 'integer'"
            v-model.number="form[key]"
            type="number"
            outlined
            dense
            :label="field.label || String(key)"
            :min="field.min"
            :max="field.max"
          />
          <q-input
            v-else-if="field.type === 'text' || field.type === 'html'"
            v-model="form[key]"
            type="textarea"
            outlined
            autogrow
            :label="field.label || String(key)"
          />
          <q-toggle
            v-else-if="field.type === 'boolean'"
            v-model="form[key]"
            :label="field.label || String(key)"
            color="primary"
          />
          <q-input
            v-else
            v-model="form[key]"
            outlined
            dense
            :label="field.label || String(key)"
          />
        </template>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn v-close-popup flat label="Отмена" />
        <q-btn color="primary" unelevated label="Сохранить" @click="save" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { IDashboardWidget, IWidgetType } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  modelValue: boolean
  widget: IDashboardWidget | null
  definition?: IWidgetType
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  save: [payload: { title: string | null; config: Record<string, any> }]
}>()

const title = ref('')
const form = reactive<Record<string, any>>({})
const schema = computed(() => props.definition?.config_schema ?? {})

watch(
  () => [props.modelValue, props.widget?.id],
  () => {
    if (!props.modelValue || !props.widget) {
      return
    }

    title.value = props.widget.title ?? ''
    Object.keys(form).forEach(key => { delete form[key] })

    for (const [key, field] of Object.entries(schema.value)) {
      form[key] = props.widget.config[key] ?? field.default ?? (field.type === 'boolean' ? false : '')
    }
  }
)

function save(): void {
  emit('save', {
    title: title.value.trim() || null,
    config: { ...form }
  })
  emit('update:modelValue', false)
}
</script>
