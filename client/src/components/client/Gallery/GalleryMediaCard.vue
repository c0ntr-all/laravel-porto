<template>
  <button
    class="media-card"
    type="button"
    :class="{ 'media-card--selected': selected }"
    @click="emit('click')"
  >
    <q-img
      class="media-card__image"
      :src="media.list_thumb_path"
      :alt="media.name"
      fit="cover"
    >
      <div v-if="isVideo" class="media-card__overlay">
        <q-icon class="media-card__play" name="play_circle" />
        <span v-if="duration" class="media-card__duration">{{ duration }}</span>
      </div>
      <div v-else-if="selected" class="media-card__overlay media-card__overlay--selected">
        <q-icon class="media-card__check" name="check_circle" />
      </div>

      <template #error>
        <div class="media-card__fallback">
          <q-icon :name="isVideo ? 'movie' : 'hide_image'" size="32px" />
        </div>
      </template>
    </q-img>

    <q-tooltip v-if="media.name" anchor="bottom middle" self="top middle">
      {{ media.name }}
    </q-tooltip>
  </button>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IGalleryMediaItem } from 'src/types/gallery'
import { formatMediaDuration, isGalleryVideo } from 'src/utils/gallery'

const props = defineProps<{
  media: IGalleryMediaItem
  selected?: boolean
}>()

const emit = defineEmits<{
  click: []
}>()

const isVideo = computed(() => isGalleryVideo(props.media))
const duration = computed(() => formatMediaDuration(props.media.duration))
</script>

<style lang="scss" scoped>
.media-card {
  position: relative;
  display: block;
  width: 100%;
  padding: 0;
  overflow: hidden;
  border: 0;
  border-radius: 12px;
  background: #ececf4;
  cursor: pointer;
  aspect-ratio: 1;

  &:hover {
    .media-card__image :deep(.q-img__image) {
      transform: scale(1.06);
    }

    .media-card__overlay {
      background: rgba(18, 18, 18, 0.38);
    }
  }

  &__image {
    height: 100%;

    :deep(.q-img__image) {
      transition: transform 0.3s ease;
    }
  }

  &__overlay {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: rgba(18, 18, 18, 0.22);
    transition: background 0.2s ease;
  }

  &__play,
  &__check {
    font-size: 42px;
    color: #fff;
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.35));
  }

  &__overlay--selected {
    background: rgba(108, 95, 252, 0.42);
  }

  &--selected {
    box-shadow: 0 0 0 3px $primary;
  }

  &__duration {
    position: absolute;
    right: 8px;
    bottom: 8px;
    padding: 2px 6px;
    border-radius: 6px;
    background: rgba(18, 18, 18, 0.78);
    color: #fff;
    font-size: 11px;
    line-height: 1.2;
  }

  &__fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
  }
}
</style>
