<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card class="tag-group-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ isEdit ? 'Edit tag group' : 'Create tag group' }}</div>
        <q-space/>
        <q-btn icon="close" flat round dense v-close-popup/>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input v-model="model.name" label="Name" filled dense/>
        <q-input
          v-model="model.slug"
          label="Slug"
          hint="Leave empty to generate from name"
          filled
          dense
        />
        <q-input
          v-model="model.description"
          label="Description"
          type="textarea"
          filled
          dense
          autogrow
        />
        <q-input
          v-model.number="model.display_order"
          label="Display order"
          type="number"
          filled
          dense
        />
        <q-toggle v-model="model.is_active" label="Active" color="primary"/>
        <q-toggle
          v-model="model.is_system"
          label="System group"
          color="primary"
          :disable="isEdit && Boolean(group?.is_system)"
        />
      </q-card-section>

      <q-card-section align="right">
        <q-btn
          :label="isEdit ? 'Save' : 'Create'"
          color="primary"
          :loading="store.isSaving"
          :disable="!model.name.trim()"
          @click="submit"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, reactive, watch } from 'vue'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import { IMusicTagGroup, IMusicTagGroupWriteDto } from 'src/types'

const props = defineProps<{
  modelValue: boolean
  group?: IMusicTagGroup | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const store = useMusicTagStore()
const isEdit = computed(() => Boolean(props.group))

const model = reactive({
  name: '',
  slug: '',
  description: '',
  display_order: 0,
  is_active: true,
  is_system: false
})

watch(() => [props.modelValue, props.group] as const, ([open]) => {
  if (!open) {
    return
  }

  model.name = props.group?.name ?? ''
  model.slug = props.group?.slug ?? ''
  model.description = props.group?.description ?? ''
  model.display_order = props.group?.display_order ?? 0
  model.is_active = props.group?.is_active ?? true
  model.is_system = props.group?.is_system ?? false
})

const payload = (): IMusicTagGroupWriteDto => ({
  name: model.name.trim(),
  slug: model.slug.trim() || null,
  description: model.description.trim() || null,
  display_order: Number(model.display_order) || 0,
  is_active: model.is_active,
  is_system: model.is_system
})

const submit = async () => {
  const saved = props.group
    ? await store.updateGroup(props.group.id, payload())
    : await store.createGroup(payload())

  if (saved) {
    emit('update:modelValue', false)
  }
}
</script>

<style lang="scss" scoped>
.tag-group-dialog {
  min-width: 480px;
  width: 100%;
}
</style>
