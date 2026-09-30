<template>
  <div
    class="media-card"
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

    <div v-if="visibleTags.length" class="media-card__tags">
      <span
        v-for="tag in visibleTags"
        :key="tag.id"
        class="media-card__tag"
      >
        {{ tag.name }}
      </span>
      <span v-if="extraTagCount" class="media-card__tag media-card__tag--more">
        +{{ extraTagCount }}
      </span>
    </div>

    <div
      v-if="showMenu"
      class="media-card__menu"
      @click.stop
    >
      <AppActionsButton :actions="actions" icon="more_vert" />
    </div>

    <q-tooltip v-if="media.name" anchor="bottom middle" self="top middle">
      {{ media.name }}
    </q-tooltip>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IGalleryMediaItem } from 'src/types/gallery'
import { IAction } from 'src/components/types'
import { formatMediaDuration, isGalleryVideo } from 'src/utils/gallery'
import AppActionsButton from 'src/components/default/AppActionsButton.vue'

const props = withDefaults(defineProps<{
  media: IGalleryMediaItem
  selected?: boolean
  showMenu?: boolean
}>(), {
  selected: false,
  showMenu: false
})

const emit = defineEmits<{
  click: []
  addTag: []
  delete: []
}>()

const isVideo = computed(() => isGalleryVideo(props.media))
const duration = computed(() => formatMediaDuration(props.media.duration))
const visibleTags = computed(() => (props.media.tags ?? []).slice(0, 2))
const extraTagCount = computed(() => Math.max(0, (props.media.tags ?? []).length - visibleTags.value.length))

const actions = computed<IAction[]>(() => [
  {
    name: 'add-tag',
    label: 'Добавить тег',
    icon: 'sell',
    is_active: true,
    func: () => emit('addTag')
  },
  {
    name: 'delete',
    label: 'Удалить',
    icon: 'delete',
    color: 'negative',
    is_active: true,
    func: () => emit('delete')
  }
])
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

    .media-card__menu {
      opacity: 1;
      pointer-events: auto;
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

  &__tags {
    position: absolute;
    left: 6px;
    right: 6px;
    bottom: 6px;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    pointer-events: none;
  }

  &__tag {
    max-width: 72px;
    padding: 1px 6px;
    overflow: hidden;
    border-radius: 999px;
    background: rgba(18, 18, 18, 0.72);
    color: #fff;
    font-size: 10px;
    line-height: 1.4;
    text-overflow: ellipsis;
    white-space: nowrap;

    &--more {
      max-width: none;
      background: rgba(108, 95, 252, 0.85);
    }
  }

  &__menu {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 2;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s ease;

    :deep(.actions-button) {
      width: 32px;
      min-height: 32px;
      color: #fff;
      background: rgba(18, 18, 18, 0.55);

      &:hover {
        background: rgba(18, 18, 18, 0.78);
      }
    }
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

@media (hover: none) {
  .media-card__menu {
    opacity: 1;
    pointer-events: auto;
  }
}
</style>
