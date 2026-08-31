<template>
  <div class="upload-web">
    <p class="upload-web__hint">
      Paste a public URL. The file stays on the remote host; the gallery only stores a reference.
    </p>

    <q-input
      v-model="link"
      label="https://example.com/photo.jpg"
      outlined
      dense
      :error="Boolean(error)"
      :error-message="error"
      @update:model-value="error = ''"
      @keyup.enter="submit"
    >
      <template #prepend>
        <q-icon name="link" />
      </template>
      <template #append>
        <q-btn-dropdown
          flat
          dense
          no-caps
          :label="kindLabel"
        >
          <q-list>
            <q-item v-close-popup clickable @click="kind = 'auto'">
              <q-item-section>Auto</q-item-section>
            </q-item>
            <q-item v-close-popup clickable @click="kind = 'photo'">
              <q-item-section>Photo</q-item-section>
            </q-item>
            <q-item v-close-popup clickable @click="kind = 'video'">
              <q-item-section>Video</q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </template>
    </q-input>

    <div v-if="canPreview" class="upload-web__preview">
      <video v-if="resolvedKind === 'video'" class="upload-web__media" :src="link.trim()" muted />
      <img v-else class="upload-web__media" :src="link.trim()" alt="Remote preview">
    </div>

    <div class="upload-web__actions">
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="add_link"
        label="Add from link"
        :loading="galleryStore.isUploading"
        :disable="!link.trim()"
        @click="submit"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { GalleryMediaKind } from 'src/types/gallery'
import { isHttpUrl, resolveMediaKind } from 'src/utils/gallery'

const emit = defineEmits<{
  done: []
}>()

const galleryStore = useGalleryStore()
const link = ref('')
const kind = ref<GalleryMediaKind | 'auto'>('auto')
const error = ref('')

const resolvedKind = computed<GalleryMediaKind>(() => (
  kind.value === 'auto' ? resolveMediaKind(link.value) : kind.value
))

const kindLabel = computed(() => (
  kind.value === 'auto'
    ? `Type: ${resolvedKind.value === 'video' ? 'Video' : 'Photo'}`
    : kind.value === 'video' ? 'Video' : 'Photo'
))

const canPreview = computed(() => isHttpUrl(link.value))

async function submit(): Promise<void> {
  const value = link.value.trim()

  if (!isHttpUrl(value)) {
    error.value = 'Enter a valid http(s) URL'
    return
  }

  try {
    await galleryStore.uploadFromWeb(value, resolvedKind.value)
    link.value = ''
    kind.value = 'auto'
    emit('done')
  } catch {
    error.value = 'Could not add this link'
  }
}
</script>

<style lang="scss" scoped>
.upload-web {
  padding: 16px 20px 20px;

  &__hint {
    margin: 0 0 16px;
    font-size: 13px;
    line-height: 1.45;
    color: #777a8f;
  }

  &__preview {
    display: flex;
    justify-content: center;
    max-height: 180px;
    margin-top: 16px;
    overflow: hidden;
    border-radius: 12px;
    background: #111;
  }

  &__media {
    max-width: 100%;
    max-height: 180px;
    object-fit: contain;
  }

  &__actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>
