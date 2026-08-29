<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card class="tag-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Delete tag</div>
        <q-space/>
        <q-btn icon="close" flat round dense v-close-popup/>
      </q-card-section>

      <q-card-section>
        Delete tag <b>{{ tag?.name }}</b> and all nested sub-tags?
      </q-card-section>

      <q-card-section align="right">
        <q-btn flat label="Cancel" v-close-popup/>
        <q-btn
          color="negative"
          label="Delete"
          :loading="store.isSaving"
          @click="removeTag"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import { IMusicTag } from 'src/types'

const props = defineProps<{
  modelValue: boolean
  tag?: IMusicTag | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const store = useMusicTagStore()

const removeTag = async () => {
  if (!props.tag) {
    return
  }

  const deleted = await store.deleteTag(props.tag.id)
  if (deleted) {
    emit('update:modelValue', false)
  }
}
</script>

<style lang="scss" scoped>
.tag-dialog {
  min-width: 400px;
}
</style>
