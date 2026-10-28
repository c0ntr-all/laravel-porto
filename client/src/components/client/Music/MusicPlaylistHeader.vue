<template>
  <div class="playlist-head q-mb-lg">
    <div class="playlist-head__left">
      <button
        type="button"
        class="playlist-head__image"
        :disabled="isImageSaving"
        @click="pickImage"
      >
        <img
          v-if="coverPreview"
          :src="coverPreview"
          :alt="playlist.name"
        >
        <div v-else class="playlist-head__placeholder">
          <q-icon name="queue_music" size="64px" color="grey-5" />
        </div>
        <div class="playlist-head__image-overlay">
          <q-spinner v-if="isImageSaving" color="white" size="28px" />
          <q-icon v-else name="photo_camera" size="28px" color="white" />
          <span>{{ isImageSaving ? 'Uploading…' : 'Change cover' }}</span>
        </div>
      </button>
      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        class="hidden-input"
        @change="onImageSelected"
      >
    </div>

    <div class="playlist-head__right">
      <div class="playlist-head__title-row">
        <q-input
          v-if="editingName"
          v-model="nameDraft"
          class="playlist-head__name-input"
          dense
          outlined
          autofocus
          maxlength="50"
          :disable="isNameSaving"
          @keyup.enter.prevent="saveName"
          @keyup.esc="cancelName"
        />
        <h2 v-else class="playlist-head__name">{{ playlist.name }}</h2>

        <div class="playlist-head__actions">
          <template v-if="editingName">
            <q-btn
              icon="check"
              color="positive"
              flat
              round
              dense
              :loading="isNameSaving"
              :disable="!nameDraft.trim()"
              @click="saveName"
            >
              <q-tooltip>Save</q-tooltip>
            </q-btn>
            <q-btn
              icon="close"
              color="negative"
              flat
              round
              dense
              :disable="isNameSaving"
              @click="cancelName"
            >
              <q-tooltip>Cancel</q-tooltip>
            </q-btn>
          </template>
          <q-btn
            v-else
            icon="edit"
            color="primary"
            flat
            round
            dense
            @click="startEditName"
          >
            <q-tooltip>Edit name</q-tooltip>
          </q-btn>
        </div>
      </div>

      <div class="playlist-head__description-row">
        <q-input
          v-if="editingDescription"
          v-model="descriptionDraft"
          class="playlist-head__description-input"
          type="textarea"
          outlined
          dense
          autogrow
          :disable="isDescriptionSaving"
          input-style="min-height: 88px; max-height: 180px"
        />
        <div
          v-else
          class="playlist-head__description"
          :class="{ 'playlist-head__description--empty': !hasDescription }"
        >
          {{ hasDescription ? playlist.description : 'описание отсутствует' }}
        </div>

        <div class="playlist-head__actions">
          <template v-if="editingDescription">
            <q-btn
              icon="check"
              color="positive"
              flat
              round
              dense
              :loading="isDescriptionSaving"
              @click="saveDescription"
            >
              <q-tooltip>Save</q-tooltip>
            </q-btn>
            <q-btn
              icon="close"
              color="negative"
              flat
              round
              dense
              :disable="isDescriptionSaving"
              @click="cancelDescription"
            >
              <q-tooltip>Cancel</q-tooltip>
            </q-btn>
          </template>
          <q-btn
            v-else
            icon="edit"
            color="primary"
            flat
            round
            dense
            @click="startEditDescription"
          >
            <q-tooltip>Edit description</q-tooltip>
          </q-btn>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useMusicPlaylistStore } from 'src/stores/modules/musicPlaylistStore'
import { IPlaylist } from 'src/types'

const props = defineProps<{
  playlist: IPlaylist
}>()

const playlistStore = useMusicPlaylistStore()

const editingName = ref(false)
const editingDescription = ref(false)
const nameDraft = ref(props.playlist.name)
const descriptionDraft = ref(props.playlist.description ?? '')
const isNameSaving = ref(false)
const isDescriptionSaving = ref(false)
const isImageSaving = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const localCoverPreview = ref<string | null>(null)

const hasDescription = computed(() => Boolean(props.playlist.description?.trim()))

const coverPreview = computed(() => (
  localCoverPreview.value || props.playlist.image || ''
))

watch(() => props.playlist.id, () => {
  editingName.value = false
  editingDescription.value = false
  nameDraft.value = props.playlist.name
  descriptionDraft.value = props.playlist.description ?? ''
  localCoverPreview.value = null
})

watch(() => props.playlist.name, (value) => {
  if (!editingName.value) {
    nameDraft.value = value
  }
})

watch(() => props.playlist.description, (value) => {
  if (!editingDescription.value) {
    descriptionDraft.value = value ?? ''
  }
})

watch(() => props.playlist.image, () => {
  localCoverPreview.value = null
})

const startEditName = () => {
  nameDraft.value = props.playlist.name
  editingName.value = true
}

const cancelName = () => {
  nameDraft.value = props.playlist.name
  editingName.value = false
}

const saveName = async () => {
  const nextName = nameDraft.value.trim()
  if (!nextName || isNameSaving.value) {
    return
  }

  if (nextName === props.playlist.name) {
    editingName.value = false
    return
  }

  isNameSaving.value = true
  const updated = await playlistStore.updatePlaylist(props.playlist.id, {
    name: nextName
  })
  isNameSaving.value = false

  if (updated) {
    editingName.value = false
  }
}

const startEditDescription = () => {
  descriptionDraft.value = props.playlist.description ?? ''
  editingDescription.value = true
}

const cancelDescription = () => {
  descriptionDraft.value = props.playlist.description ?? ''
  editingDescription.value = false
}

const saveDescription = async () => {
  if (isDescriptionSaving.value) {
    return
  }

  const nextDescription = descriptionDraft.value.trim() || null
  const currentDescription = props.playlist.description?.trim() || null

  if (nextDescription === currentDescription) {
    editingDescription.value = false
    return
  }

  isDescriptionSaving.value = true
  const updated = await playlistStore.updatePlaylist(props.playlist.id, {
    description: nextDescription
  })
  isDescriptionSaving.value = false

  if (updated) {
    editingDescription.value = false
  }
}

const pickImage = () => {
  if (isImageSaving.value) {
    return
  }

  fileInput.value?.click()
}

const onImageSelected = async (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''

  if (!file) {
    return
  }

  localCoverPreview.value = URL.createObjectURL(file)
  isImageSaving.value = true

  const updated = await playlistStore.updatePlaylist(props.playlist.id, {
    image_file: file
  })

  isImageSaving.value = false

  if (!updated) {
    localCoverPreview.value = null
  }
}
</script>

<style lang="scss" scoped>
.playlist-head {
  display: flex;
  column-gap: 1.25rem;
  padding: 1rem 0 0;

  &__left {
    flex: 0 0 auto;
  }

  &__image {
    position: relative;
    display: block;
    width: 200px;
    height: 200px;
    padding: 0;
    border: none;
    border-radius: 8px;
    overflow: hidden;
    background: #f0f2f5;
    cursor: pointer;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    &:hover .playlist-head__image-overlay,
    &:focus-visible .playlist-head__image-overlay {
      opacity: 1;
    }

    &:disabled {
      cursor: wait;
    }
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
  }

  &__image-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: rgba(0, 0, 0, 0.45);
    color: #fff;
    font-size: 0.8rem;
    opacity: 0;
    transition: opacity 0.15s ease;
  }

  &__right {
    flex: 1 1 auto;
    min-width: 0;
  }

  &__title-row,
  &__description-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
  }

  &__title-row {
    margin-bottom: 0.75rem;
  }

  &__name {
    margin: 0;
    flex: 1 1 auto;
    min-width: 0;
    font-size: 45px;
    line-height: 1.05;
    font-weight: 700;
  }

  &__name-input {
    flex: 1 1 auto;
    min-width: 0;

    :deep(.q-field__control) {
      font-size: 1.5rem;
      font-weight: 700;
    }
  }

  &__description {
    flex: 1 1 auto;
    min-width: 0;
    margin: 0;
    font-size: 1rem;
    line-height: 1.45;
    white-space: pre-wrap;
    word-break: break-word;

    &--empty {
      color: #9e9e9e;
      font-style: italic;
    }
  }

  &__description-input {
    flex: 1 1 auto;
    min-width: 0;
    max-width: 560px;
  }

  &__actions {
    display: flex;
    align-items: center;
    flex: 0 0 auto;
    margin-top: 4px;
  }
}

.hidden-input {
  display: none;
}

@media (max-width: 599px) {
  .playlist-head {
    flex-direction: column;
    row-gap: 1rem;

    &__image {
      width: 160px;
      height: 160px;
    }

    &__name {
      font-size: 2rem;
    }
  }
}
</style>
