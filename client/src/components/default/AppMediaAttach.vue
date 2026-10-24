<template>
  <div class="app-media-attach">
    <slot :open="openSource">
      <q-btn
        v-bind="$attrs"
        class="app-media-attach__button"
        no-caps
        :outline="outline"
        :flat="flat"
        :unelevated="unelevated"
        :dense="dense"
        :color="color"
        :icon="icon"
        :label="label"
        :disable="disable"
        @click="openSource"
      />
    </slot>

    <input
      ref="fileInputRef"
      class="app-media-attach__input"
      type="file"
      :accept="accept"
      :multiple="multiple"
      @change="onFilesPicked"
    >

    <q-dialog v-model="sourceOpen">
      <q-card class="media-attach-dialog">
        <header class="media-attach-dialog__header">
          <h2 class="media-attach-dialog__title">
            Прикрепить файл
          </h2>
          <q-btn
            flat
            round
            dense
            icon="close"
            aria-label="Закрыть"
            @click="sourceOpen = false"
          />
        </header>

        <q-separator />

        <div class="media-attach-dialog__body">
          <button
            type="button"
            class="source-option"
            @click="chooseDevice"
          >
            <span class="source-option__icon">
              <q-icon name="folder_open" size="22px" />
            </span>
            <span class="source-option__text">
              <span class="source-option__title">С устройства</span>
              <span class="source-option__hint">Файлы с этого устройства</span>
            </span>
            <q-icon name="chevron_right" color="grey-5" size="22px" />
          </button>

          <button
            type="button"
            class="source-option"
            :disabled="limit < 1"
            @click="chooseGallery"
          >
            <span class="source-option__icon">
              <q-icon name="photo_library" size="22px" />
            </span>
            <span class="source-option__text">
              <span class="source-option__title">Фото</span>
              <span class="source-option__hint">
                {{ limit < 1 ? 'Достигнут лимит' : `До ${limit} фото из альбомов галереи` }}
              </span>
            </span>
            <q-icon name="chevron_right" color="grey-5" size="22px" />
          </button>
        </div>
      </q-card>
    </q-dialog>

    <AppMediaAttachGallery
      v-model="galleryOpen"
      :limit="limit"
      :exclude-ids="excludeIds"
      @select="onGallerySelect"
    />
  </div>
</template>

<script lang="ts" setup>
import { nextTick, ref } from 'vue'
import { IGalleryMediaItem } from 'src/types/gallery'
import AppMediaAttachGallery from 'src/components/default/AppMediaAttachGallery.vue'

defineOptions({
  inheritAttrs: false
})

const props = withDefaults(defineProps<{
  limit?: number
  accept?: string
  multiple?: boolean
  excludeIds?: string[]
  label?: string
  icon?: string
  color?: string
  flat?: boolean
  outline?: boolean
  unelevated?: boolean
  dense?: boolean
  disable?: boolean
}>(), {
  limit: 10,
  accept: 'image/*,video/*',
  multiple: true,
  excludeIds: () => [],
  label: 'Прикрепить файл',
  icon: 'attach_file',
  color: 'primary',
  flat: false,
  outline: true,
  unelevated: false,
  dense: false,
  disable: false
})

const emit = defineEmits<{
  'select-files': [files: File[]]
  'select-gallery': [items: IGalleryMediaItem[]]
}>()

const sourceOpen = ref(false)
const galleryOpen = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)

function openSource() {
  if (props.disable) {
    return
  }

  sourceOpen.value = true
}

function chooseDevice() {
  fileInputRef.value?.click()
}

async function chooseGallery() {
  if (props.limit < 1) {
    return
  }

  sourceOpen.value = false
  await nextTick()
  galleryOpen.value = true
}

function onFilesPicked(event: Event) {
  const input = event.target as HTMLInputElement
  const files = input.files ? Array.from(input.files) : []
  input.value = ''

  if (!files.length) {
    return
  }

  sourceOpen.value = false
  emit('select-files', files)
}

function onGallerySelect(items: IGalleryMediaItem[]) {
  if (!items.length) {
    return
  }

  emit('select-gallery', items)
}
</script>

<style lang="scss" scoped>
.app-media-attach {
  position: relative;
}

.app-media-attach__input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.media-attach-dialog {
  width: min(440px, 92vw);
  border-radius: 16px;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 8px 14px 18px;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #282f53;
  }

  &__body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
  }
}

.source-option {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  padding: 12px;
  border: 1px solid #e4e7eb;
  border-radius: 14px;
  background: #fff;
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;

  &:hover:not(:disabled) {
    border-color: $primary;
    background: rgba(108, 95, 252, 0.04);
  }

  &:focus-visible {
    outline: 2px solid $primary;
    outline-offset: 2px;
  }

  &:disabled {
    cursor: default;
    opacity: 0.5;
  }

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    flex: none;
    border-radius: 12px;
    background: rgba(108, 95, 252, 0.12);
    color: $primary;
  }

  &__text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1;
  }

  &__title {
    font-weight: 600;
    color: #282f53;
  }

  &__hint {
    color: #777a8f;
    font-size: 13px;
  }
}
</style>
