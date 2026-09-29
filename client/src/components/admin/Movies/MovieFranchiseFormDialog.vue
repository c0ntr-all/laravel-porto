<template>
  <q-dialog v-model="open">
    <q-card class="movie-franchise-form" style="min-width: 480px; max-width: 640px; width: 100%">
      <q-card-section class="text-h6">
        {{ franchise ? 'Edit franchise' : 'Create franchise' }}
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input
          v-model="name"
          label="Name"
          outlined
          dense
          :rules="[value => Boolean(String(value ?? '').trim()) || 'Required']"
        />

        <div>
          <div class="text-caption text-grey-7 q-mb-xs">Description</div>
          <q-editor
            v-model="description"
            min-height="8rem"
            :toolbar="editorToolbar"
          />
        </div>

        <div class="movie-franchise-form__image">
          <div v-if="previewUrl" class="movie-franchise-form__preview">
            <q-img :src="previewUrl" fit="cover" />
          </div>
          <q-file
            v-model="imageFile"
            label="Cover image"
            outlined
            dense
            clearable
            accept="image/*"
          />
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn
          color="primary"
          unelevated
          label="Save"
          :loading="store.isSaving"
          :disable="!canSave"
          @click="save"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useMovieFranchiseStore } from 'src/stores/modules/movieFranchiseStore'
import { IMovieFranchise } from 'src/types/Movie'

const props = defineProps<{
  modelValue: boolean
  franchise?: IMovieFranchise | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const store = useMovieFranchiseStore()
const name = ref('')
const description = ref('')
const imageFile = ref<File | null>(null)

const editorToolbar = [
  ['bold', 'italic', 'underline'],
  ['unordered', 'ordered'],
  ['link'],
  ['undo', 'redo']
]

const open = computed({
  get: () => props.modelValue,
  set: (value: boolean) => emit('update:modelValue', value)
})

const previewUrl = computed(() => {
  if (imageFile.value) {
    return URL.createObjectURL(imageFile.value)
  }

  return props.franchise?.image ?? null
})

const canSave = computed(() => Boolean(name.value.trim()) && !store.isSaving)

function descriptionPayload(html: string): string | null {
  const text = html
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim()

  return text ? html : null
}

watch(
  () => [props.modelValue, props.franchise] as const,
  ([visible, franchise]) => {
    if (!visible) {
      return
    }

    name.value = franchise?.name ?? ''
    description.value = franchise?.description ?? ''
    imageFile.value = null
  },
  { immediate: true }
)

async function save(): Promise<void> {
  const payload = {
    name: name.value.trim(),
    description: descriptionPayload(description.value),
    image_file: imageFile.value
  }

  const result = props.franchise
    ? await store.updateFranchise(props.franchise.id, payload)
    : await store.createFranchise(payload)

  if (result) {
    emit('saved')
    open.value = false
  }
}
</script>

<style lang="scss" scoped>
.movie-franchise-form {
  &__preview {
    width: 120px;
    aspect-ratio: 2 / 3;
    margin-bottom: 8px;
    overflow: hidden;
    border-radius: 10px;
    background: rgba(40, 47, 83, 0.06);

    :deep(.q-img) {
      height: 100%;
    }
  }
}
</style>
