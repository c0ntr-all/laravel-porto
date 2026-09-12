<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card class="genre-form-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ genre ? 'Edit genre' : 'Create genre' }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input v-model="model.name" label="Name" filled dense />
        <q-input
          v-model="model.slug"
          label="Slug"
          hint="Leave empty to generate from name"
          filled
          dense
        />
        <q-input
          v-model.number="model.kp_id"
          label="Kinopoisk ID"
          type="number"
          filled
          dense
          hint="Optional"
        />
      </q-card-section>

      <q-card-section align="right">
        <q-btn
          :label="genre ? 'Save' : 'Create'"
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
import { reactive, watch } from 'vue'
import { useMovieGenreStore } from 'src/stores/modules/movieGenreStore'
import { IMovieGenre, IMovieGenreWriteDto } from 'src/types/Movie'

const props = defineProps<{
  modelValue: boolean
  genre?: IMovieGenre | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const store = useMovieGenreStore()

const model = reactive({
  name: '',
  slug: '',
  kp_id: null as number | null
})

watch(() => [props.modelValue, props.genre] as const, ([open]) => {
  if (!open) {
    return
  }

  model.name = props.genre?.name ?? ''
  model.slug = props.genre?.slug ?? ''
  model.kp_id = props.genre?.kp_id ?? null
})

function payload(): IMovieGenreWriteDto {
  const kpId = Number(model.kp_id)

  return {
    name: model.name.trim(),
    slug: model.slug.trim() || null,
    kp_id: Number.isInteger(kpId) && kpId >= 1 ? kpId : null
  }
}

async function submit(): Promise<void> {
  const saved = props.genre
    ? await store.updateGenre(props.genre.id, payload())
    : await store.createGenre(payload())

  if (saved) {
    emit('update:modelValue', false)
  }
}
</script>

<style lang="scss" scoped>
.genre-form-dialog {
  min-width: 420px;
  width: 100%;
}
</style>
