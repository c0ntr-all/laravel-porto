<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card class="tag-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ title }}</div>
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
        <q-select
          v-model="model.group_id"
          :options="groupOptions"
          label="Tag group"
          emit-value
          map-options
          filled
          dense
          :disable="Boolean(parent)"
          :hint="parent ? 'Inherited from parent tag' : undefined"
        />
        <q-select
          v-if="!parent"
          v-model="model.parent_id"
          :options="parentSelectOptions"
          label="Parent tag"
          emit-value
          map-options
          clearable
          filled
          dense
          hint="Optional. Creates a sub-tag when set."
        />
        <q-toggle v-model="model.is_active" label="Active" color="primary"/>
      </q-card-section>

      <q-card-section align="right">
        <q-btn
          :label="tag ? 'Save' : 'Create'"
          color="primary"
          :loading="store.isSaving"
          :disable="!canSubmit"
          @click="submit"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, reactive, watch } from 'vue'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import { IMusicTag, IMusicTagWriteDto } from 'src/types'

const props = defineProps<{
  modelValue: boolean
  tag?: IMusicTag | null
  parent?: IMusicTag | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const store = useMusicTagStore()

const model = reactive({
  name: '',
  slug: '',
  description: '',
  group_id: null as string | null,
  parent_id: null as string | null,
  is_active: true
})

const title = computed(() => {
  if (props.tag) {
    return `Edit tag ${props.tag.name}`
  }
  if (props.parent) {
    return `Create child tag for ${props.parent.name}`
  }

  return 'Create tag'
})

const groupOptions = computed(() => store.groups.map(group => ({
  label: group.name,
  value: group.id
})))

const parentSelectOptions = computed(() => (
  store.parentOptions(model.group_id, props.tag?.id).map(tag => ({
    label: tag.name,
    value: tag.id
  }))
))

const canSubmit = computed(() => (
  Boolean(model.name.trim() && (model.group_id || model.parent_id || props.parent))
))

watch(() => [props.modelValue, props.tag, props.parent] as const, ([open]) => {
  if (!open) {
    return
  }

  model.name = props.tag?.name ?? ''
  model.slug = props.tag?.slug ?? ''
  model.description = props.tag?.description ?? ''
  model.group_id = props.tag?.group_id ?? props.parent?.group_id ?? null
  model.parent_id = props.tag?.parent_id ?? props.parent?.id ?? null
  model.is_active = props.tag?.is_active ?? true
})

watch(() => model.group_id, (groupId, previous) => {
  if (props.parent || !previous || groupId === previous) {
    return
  }

  if (model.parent_id && !store.parentOptions(groupId).some(tag => tag.id === model.parent_id)) {
    model.parent_id = null
  }
})

const toId = (value: string | null): number | null => (
  value == null || value === '' ? null : Number(value)
)

const payload = (): IMusicTagWriteDto => ({
  name: model.name.trim(),
  slug: model.slug.trim() || null,
  description: model.description.trim() || null,
  group_id: toId(model.group_id),
  parent_id: toId(props.parent?.id ?? model.parent_id),
  is_active: model.is_active
})

const submit = async () => {
  const saved = props.tag
    ? await store.updateTag(props.tag.id, payload())
    : await store.createTag(payload())

  if (saved) {
    emit('update:modelValue', false)
  }
}
</script>

<style lang="scss" scoped>
.tag-dialog {
  min-width: 480px;
  width: 100%;
}
</style>
