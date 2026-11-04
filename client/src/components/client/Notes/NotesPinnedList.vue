<template>
  <section
    v-if="pinnedNotes.length"
    class="notes-pinned"
  >
    <h2 class="notes-pinned__title">
      <q-icon
        name="push_pin"
        size="16px"
        color="primary"
      />
      Закреплённые
    </h2>

    <div
      ref="listRef"
      class="notes-pinned__list"
      :class="{ 'notes-pinned__list--sorting': isSorting }"
    >
      <div
        v-for="(note, index) in displayItems"
        :key="note.id"
        class="notes-pinned__row"
        :style="rowStyle(index)"
      >
        <NoteRow
          :note="note"
          :active="route.params.id === note.id"
          @open="open(note.id)"
          @create-child="emit('create-child', note.id)"
          @move="emit('move', note.id)"
          @pin="pin(note)"
          @remove="emit('remove', note.id)"
        >
          <template #prefix>
            <q-btn
              class="notes-pinned__handle"
              icon="drag_indicator"
              flat
              dense
              round
              size="sm"
              @pointerdown="onHandlePointerDown($event, note.id, index)"
            />
          </template>
        </NoteRow>
      </div>
    </div>
  </section>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { INote } from 'src/types'
import { useNoteStore } from 'src/stores/modules/noteStore'
import { useVerticalReorder } from 'src/composables/client/TaskManager/useVerticalReorder'
import NoteRow from 'src/components/client/Notes/NoteRow.vue'

const emit = defineEmits<{
  'create-child': [id: string]
  move: [id: string]
  remove: [id: string]
}>()

const store = useNoteStore()
const route = useRoute()
const router = useRouter()
const listRef = ref<HTMLElement | null>(null)
let draggedId = ''

const pinnedNotes = computed(() => store.pinnedIds
  .map(id => store.notes.byId[id])
  .filter((note): note is INote => Boolean(note)))

const {
  displayItems,
  isSorting,
  rowStyle,
  onPointerDown
} = useVerticalReorder(listRef, {
  items: pinnedNotes,
  rowSelector: '.notes-pinned__row',
  onReorder: async (orderedIds) => {
    const movedId = draggedId
    const index = orderedIds.indexOf(movedId)
    if (!movedId || index < 0) return false

    const nextId = orderedIds[index + 1]
    const beforeId = nextId == null ? null : Number(nextId)
    if (beforeId != null && !Number.isInteger(beforeId)) return false

    return store.movePinned(movedId, beforeId)
  }
})

function onHandlePointerDown (event: PointerEvent, id: string, index: number): void {
  draggedId = id
  onPointerDown(event, id, index)
}

function pin (note: INote): void {
  void store.setPinned(note.id, !note.is_pinned)
}

function open (id: string): void {
  void router.push({ name: 'note', params: { id } })
}
</script>

<style lang="scss" scoped>
.notes-pinned {
  margin-bottom: 12px;

  &__title {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 8px 6px;
    font-size: 13px;
    font-weight: 600;
    color: #5c6278;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 2px;

    &--sorting {
      user-select: none;
    }
  }

  &__handle {
    width: 28px;
    min-width: 28px;
    cursor: grab;
    touch-action: none;
  }
}
</style>
