<template>
  <q-btn
    color="primary"
    unelevated
    no-caps
    icon="add"
    label="Create album"
    @click="show = true"
  />

  <q-dialog v-model="show">
    <q-card class="create-album">
      <q-card-section class="create-album__header">
        <div class="create-album__title">New album</div>
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-gutter-md">
        <q-input
          v-model="name"
          label="Name"
          outlined
          dense
          autofocus
          :error="Boolean(error)"
          :error-message="error"
          @keyup.enter="submit"
        />
        <q-input
          v-model="description"
          label="Description"
          type="textarea"
          outlined
          dense
          autogrow
        />
      </q-card-section>

      <q-separator />

      <q-card-section class="create-album__actions">
        <q-btn flat no-caps v-close-popup>Cancel</q-btn>
        <q-btn
          color="primary"
          unelevated
          no-caps
          label="Create"
          :disable="!name.trim()"
          :loading="galleryStore.isSaving"
          @click="submit"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import { useGalleryStore } from 'src/stores/modules/galleryStore'

const galleryStore = useGalleryStore()
const show = ref(false)
const name = ref('')
const description = ref('')
const error = ref('')

watch(show, (visible) => {
  if (visible) {
    name.value = ''
    description.value = ''
    error.value = ''
  }
})

async function submit(): Promise<void> {
  const value = name.value.trim()

  if (!value) {
    error.value = 'Enter an album name'
    return
  }

  const created = await galleryStore.createAlbum({
    name: value,
    description: description.value.trim() || null
  })

  if (created) {
    show.value = false
  }
}
</script>

<style lang="scss" scoped>
.create-album {
  width: 460px;
  max-width: 92vw;
  border-radius: 16px;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  &__title {
    font-size: 18px;
    font-weight: 600;
    color: #282f53;
  }

  &__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
  }
}
</style>
