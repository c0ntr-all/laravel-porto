<template>
  <div
    class="note-row"
    :class="{ 'note-row--active': active }"
    :style="{ '--depth': String(depth) }"
  >
    <slot name="prefix">
      <q-btn
        v-if="showExpand"
        class="note-row__toggle"
        flat
        dense
        round
        size="sm"
        :icon="expanded ? 'expand_more' : 'chevron_right'"
        @click="emit('toggle')"
      />
      <span
        v-else
        class="note-row__spacer"
      />
    </slot>

    <button
      type="button"
      class="note-row__main"
      @click="emit('open')"
    >
      <span class="note-row__name">
        <q-icon
          v-if="note.is_pinned"
          name="push_pin"
          size="14px"
          color="primary"
        />
        {{ note.name }}
      </span>
      <span
        v-if="note.excerpt"
        class="note-row__excerpt"
      >{{ note.excerpt }}</span>
    </button>

    <span
      v-if="note.children_count"
      class="note-row__count"
    >{{ note.children_count }}</span>

    <q-btn
      icon="more_vert"
      flat
      dense
      round
      size="sm"
      @click.stop
    >
      <q-menu
        anchor="bottom right"
        self="top right"
        auto-close
      >
        <q-list dense>
          <q-item
            clickable
            @click="emit('create-child')"
          >
            <q-item-section>Дочерняя заметка</q-item-section>
          </q-item>
          <q-item
            clickable
            @click="emit('move')"
          >
            <q-item-section>Переместить</q-item-section>
          </q-item>
          <q-item
            clickable
            @click="emit('pin')"
          >
            <q-item-section>{{ note.is_pinned ? 'Открепить' : 'Закрепить' }}</q-item-section>
          </q-item>
          <q-item
            clickable
            @click="emit('remove')"
          >
            <q-item-section class="text-negative">Удалить</q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </q-btn>
  </div>
</template>

<script lang="ts" setup>
import { INote } from 'src/types'

withDefaults(defineProps<{
  note: INote
  active?: boolean
  depth?: number
  showExpand?: boolean
  expanded?: boolean
}>(), {
  active: false,
  depth: 0,
  showExpand: false,
  expanded: false
})

const emit = defineEmits<{
  open: []
  toggle: []
  'create-child': []
  move: []
  pin: []
  remove: []
}>()
</script>

<style lang="scss" scoped>
.note-row {
  display: flex;
  align-items: flex-start;
  gap: 2px;
  padding: 6px 8px 6px calc(8px + var(--depth) * 16px);
  border-radius: 10px;

  &--active {
    background: rgba(108, 95, 252, 0.1);
  }

  &__toggle,
  &__spacer {
    width: 28px;
    min-width: 28px;
    height: 28px;
  }

  &__main {
    flex: 1;
    min-width: 0;
    padding: 2px 4px;
    border: 0;
    background: transparent;
    text-align: left;
    cursor: pointer;
    color: inherit;
  }

  &__name {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 600;
    color: #282f53;
    word-break: break-word;
  }

  &__excerpt {
    display: block;
    margin-top: 2px;
    color: #777a8f;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__count {
    margin-top: 6px;
    min-width: 18px;
    padding: 0 6px;
    border-radius: 999px;
    background: #eef0f6;
    color: #5c6278;
    font-size: 11px;
    line-height: 18px;
    text-align: center;
  }
}
</style>
