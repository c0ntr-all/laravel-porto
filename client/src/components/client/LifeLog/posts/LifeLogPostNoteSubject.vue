<template>
  <div class="ll-note-subject">
    <div class="ll-note-subject__icon">
      <q-icon name="sticky_note_2" size="22px" />
    </div>
    <div class="ll-note-subject__body">
      <div class="ll-note-subject__title">
        {{ note.name || 'Без названия' }}
      </div>
      <div
        v-if="note.excerpt"
        class="ll-note-subject__excerpt"
      >
        {{ note.excerpt }}
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IPostNoteSubject } from 'src/types'

defineProps<{
  note: IPostNoteSubject
}>()
</script>

<style scoped lang="scss">
.ll-note-subject {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  background: linear-gradient(135deg, #fffaf0 0%, #ffffff 55%);

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    flex: none;
    border-radius: 10px;
    background: rgba(242, 192, 55, 0.18);
    color: #c48a12;
  }

  &__body {
    min-width: 0;
    flex: 1;
  }

  &__title {
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.35;
    color: #1f2937;
  }

  &__excerpt {
    margin-top: 4px;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}
</style>
