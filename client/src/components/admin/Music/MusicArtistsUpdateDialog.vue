<template>
  <q-dialog v-model="show">
    <q-card class="artist-dialog">
      <q-card-section class="row items-center">
        <div class="text-h6">Edit artist</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row q-col-gutter-lg">
          <div class="col-12 col-sm-4">
            <div class="artist-dialog__cover q-mb-sm">
              <q-img
                v-if="coverPreview"
                :src="coverPreview"
                ratio="1"
              />
              <div v-else class="artist-dialog__placeholder">
                <q-icon name="person" size="64px" color="grey-5" />
              </div>
            </div>
            <q-file
              v-model="imageFile"
              label="Cover image"
              accept="image/*"
              outlined
              dense
              clearable
            >
              <template #prepend>
                <q-icon name="image" />
              </template>
            </q-file>
          </div>
          <div class="col-12 col-sm-8">
            <q-input
              v-model="name"
              label="Name"
              outlined
              dense
              class="q-mb-md"
              :rules="[val => Boolean(val && String(val).trim()) || 'Name is required']"
            />
            <q-input
              v-model="description"
              label="Description"
              type="textarea"
              autogrow
              outlined
              dense
              class="q-mb-md"
            />
            <div class="text-subtitle2 q-mb-sm">Tags</div>
            <div
              v-if="tagsUnlocked"
              class="artist-dialog__tags-note q-mb-sm"
            >
              <q-icon name="warning_amber" size="18px" color="warning" />
              <span>Изменения применятся ко всем альбомам и трекам этого исполнителя.</span>
            </div>
            <div
              class="artist-dialog__tags"
              :class="{ 'is-locked': !tagsUnlocked }"
            >
              <div
                v-if="!tagsUnlocked"
                class="artist-dialog__tags-banner"
                aria-hidden="true"
              >
                <q-icon name="lock" size="16px" />
                <span>Нажмите, чтобы изменить</span>
              </div>
              <MusicTagGroupSelect
                v-model="tagIds"
                :disable="!tagsUnlocked"
              />
              <button
                v-if="!tagsUnlocked"
                type="button"
                class="artist-dialog__tags-shield"
                aria-label="Нажмите, чтобы изменить теги"
                @click="showTagsConfirm = true"
              />
            </div>
          </div>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-actions align="right">
        <q-btn flat no-caps v-close-popup>Cancel</q-btn>
        <q-btn
          color="primary"
          unelevated
          no-caps
          :loading="admin.isArtistSaving"
          :disable="!name.trim()"
          @click="save"
        >
          Save
        </q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>

  <q-dialog v-model="showTagsConfirm" persistent>
    <q-card class="artist-tags-confirm">
      <q-card-section class="row items-start no-wrap q-gutter-md">
        <q-avatar
          icon="warning_amber"
          color="warning"
          text-color="dark"
          font-size="22px"
        />
        <div class="artist-tags-confirm__text">
          Изменение тегов у исполнителя применится ко всем альбомам и трекам этого исполнителя! Продолжить?
        </div>
      </q-card-section>
      <q-card-actions align="right" class="q-px-md q-pb-md">
        <q-btn
          flat
          no-caps
          label="Нет"
          color="grey-8"
          autofocus
          v-close-popup
        />
        <q-btn
          unelevated
          no-caps
          label="Да"
          color="warning"
          text-color="dark"
          @click="unlockTags"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useMusicAdminStore } from 'src/stores/modules/musicAdminStore'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import MusicTagGroupSelect from 'src/components/admin/Music/MusicTagGroupSelect.vue'
import { IArtist } from 'src/types'

const props = defineProps<{
  modelValue: boolean
  artist: IArtist
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: [artist: IArtist]
}>()

const admin = useMusicAdminStore()
const tagStore = useMusicTagStore()
const show = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})
const name = ref(props.artist.name)
const description = ref(props.artist.description ?? '')
const tagIds = ref(props.artist.tags.map(tag => tag.id))
const imageFile = ref<File | null>(null)
const tagsUnlocked = ref(false)
const showTagsConfirm = ref(false)

const coverPreview = computed(() => {
  if (imageFile.value) {
    return URL.createObjectURL(imageFile.value)
  }

  return props.artist.image || ''
})

watch(() => props.artist, artist => {
  name.value = artist.name
  description.value = artist.description ?? ''
  tagIds.value = artist.tags.map(tag => tag.id)
  imageFile.value = null
  tagsUnlocked.value = false
  showTagsConfirm.value = false
})

watch(show, open => {
  tagsUnlocked.value = false

  if (!open) {
    showTagsConfirm.value = false
  }
})

const unlockTags = () => {
  tagsUnlocked.value = true
  showTagsConfirm.value = false
}

const save = async () => {
  const artist = await admin.updateArtist(props.artist.id, {
    name: name.value.trim(),
    description: description.value.trim() || null,
    tags: tagIds.value,
    image_file: imageFile.value
  })

  if (artist) {
    emit('saved', artist)
  }
}

onMounted(() => {
  if (!tagStore.groups.length || !tagStore.tags.length) {
    void tagStore.loadAll()
  }
})
</script>

<style lang="scss" scoped>
.artist-dialog {
  width: 760px;
  max-width: 92vw;

  &__cover {
    overflow: hidden;
    border-radius: 8px;
    background: #f0f2f5;
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 1;
  }

  &__tags {
    position: relative;

    &.is-locked :deep(.q-field--disabled) {
      opacity: 1;

      .q-field__control {
        background: #f6f7f9;
      }

      .q-field__native,
      .q-field__label,
      .q-chip {
        color: inherit;
        opacity: 1;
      }
    }

    :deep(.q-gutter-md) {
      margin-top: 0;
    }
  }

  &__tags-banner {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    color: #5f6368;
    font-size: 12px;
    line-height: 16px;
  }

  &__tags-shield {
    position: absolute;
    z-index: 2;
    inset: 0;
    margin: 0;
    padding: 0;
    border: 1px dashed rgba(0, 0, 0, 0.22);
    border-radius: 8px;
    background: transparent;
    cursor: pointer;

    &:hover,
    &:focus-visible {
      border-color: #f2c037;
      background: rgba(255, 243, 205, 0.28);
      outline: none;
    }
  }

  &__tags-note {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 8px;
    background: #fff6e0;
    color: #7a4e00;
    font-size: 13px;
    line-height: 1.35;
  }
}

.artist-tags-confirm {
  width: 480px;
  max-width: 92vw;

  &__text {
    padding-top: 6px;
    line-height: 1.45;
  }
}
</style>
