<template>
  <div class="profile-avatar">
    <button
      class="profile-avatar__button"
      :class="{ 'profile-avatar__button--dragging': isDragging }"
      type="button"
      :disabled="busy"
      @click="openPicker"
      @dragenter.prevent="isDragging = true"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
    >
      <AppUserAvatar
        :user="user"
        size="96px"
      />
      <span class="profile-avatar__overlay">
        <q-icon name="photo_camera" size="22px" />
        <span class="profile-avatar__hint">{{ user.avatar ? 'Сменить' : 'Загрузить' }}</span>
      </span>
      <q-inner-loading :showing="busy" color="primary" />
    </button>

    <q-btn
      v-if="user.avatar"
      class="profile-avatar__remove"
      icon="close"
      color="negative"
      size="sm"
      round
      dense
      unelevated
      :disable="busy"
      @click.stop="onRemove"
    >
      <q-tooltip>Удалить фото</q-tooltip>
    </q-btn>

    <input
      ref="fileInput"
      class="hidden"
      type="file"
      :accept="GALLERY_IMAGE_ACCEPT"
      @change="onFileInput"
    >
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { Dialog, Notify } from 'quasar'
import { useUserStore } from 'src/stores/modules/userStore'
import { GALLERY_IMAGE_ACCEPT, isVideoFile } from 'src/utils/gallery'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'

const MAX_SIZE_BYTES = 5 * 1024 * 1024

const userStore = useUserStore()
const { user, isAvatarBusy } = storeToRefs(userStore)
const fileInput = ref<HTMLInputElement | null>(null)
const isDragging = ref(false)
const busy = isAvatarBusy

const openPicker = () => {
  if (!busy.value) {
    fileInput.value?.click()
  }
}

const onFileInput = (event: Event) => {
  const input = event.target as HTMLInputElement
  void uploadFile(input.files?.[0] ?? null)
  input.value = ''
}

const onDrop = (event: DragEvent) => {
  isDragging.value = false
  void uploadFile(event.dataTransfer?.files?.[0] ?? null)
}

const uploadFile = async (file: File | null) => {
  if (!file || busy.value) {
    return
  }

  if (isVideoFile(file)) {
    Notify.create({
      type: 'warning',
      message: 'Аватар должен быть изображением'
    })
    return
  }

  if (file.size > MAX_SIZE_BYTES) {
    Notify.create({
      type: 'warning',
      message: 'Максимальный размер файла — 5 МБ'
    })
    return
  }

  try {
    await userStore.uploadAvatar(file)
  } catch {
    // Notification is handled in the store
  }
}

const onRemove = () => {
  Dialog.create({
    title: 'Удалить фото?',
    message: 'Аватар будет убран из профиля.',
    cancel: {
      label: 'Отмена',
      flat: true,
      noCaps: true
    },
    ok: {
      label: 'Удалить',
      color: 'negative',
      unelevated: true,
      noCaps: true
    },
    persistent: true
  }).onOk(async () => {
    try {
      await userStore.deleteAvatar()
    } catch {
      // Notification is handled in the store
    }
  })
}
</script>

<style lang="scss" scoped>
.profile-avatar {
  position: relative;
  width: 96px;
  height: 96px;
  flex: none;

  &__button {
    position: relative;
    width: 96px;
    height: 96px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    background: transparent;
  }

  &__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    color: #fff;
    background: rgba(40, 47, 83, 0.55);
    opacity: 0;
    transition: opacity 0.15s ease;
  }

  &__hint {
    font-size: 11px;
    line-height: 1;
  }

  &__button:hover &__overlay,
  &__button:focus-visible &__overlay,
  &__button--dragging &__overlay {
    opacity: 1;
  }

  &__remove {
    position: absolute;
    top: -4px;
    right: -4px;
    z-index: 1;
  }
}

.hidden {
  display: none;
}
</style>
