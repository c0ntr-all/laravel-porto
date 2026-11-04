<template>
  <div v-if="note">
    <NoteRow
      :note="note"
      :active="active"
      :depth="depth"
      :show-expand="note.children_count > 0"
      :expanded="isExpanded"
      @open="open"
      @toggle="toggle"
      @create-child="emit('create-child', note.id)"
      @move="emit('move', note.id)"
      @pin="togglePin"
      @remove="emit('remove', note.id)"
    />

    <div
      v-if="isExpanded"
      class="notes-tree-node__children"
    >
      <div
        v-if="store.childLoading[note.id] && !childIds.length"
        class="notes-tree-node__loading"
      >
        <q-spinner
          color="primary"
          size="18px"
        />
      </div>

      <NotesTreeNode
        v-for="childId in childIds"
        :key="childId"
        :note-id="childId"
        :depth="depth + 1"
        @create-child="emit('create-child', $event)"
        @move="emit('move', $event)"
        @remove="emit('remove', $event)"
      />

      <NotesLoadMore
        :enabled="Boolean(store.childrenHasMore[note.id])"
        :loading="Boolean(store.childLoading[note.id])"
        @load="loadMore"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useNoteStore } from 'src/stores/modules/noteStore'
import NoteRow from 'src/components/client/Notes/NoteRow.vue'
import NotesLoadMore from 'src/components/client/Notes/NotesLoadMore.vue'

defineOptions({
  name: 'NotesTreeNode'
})

const props = defineProps<{
  noteId: string
  depth?: number
}>()

const emit = defineEmits<{
  'create-child': [id: string]
  move: [id: string]
  remove: [id: string]
}>()

const store = useNoteStore()
const route = useRoute()
const router = useRouter()

const depth = computed(() => props.depth ?? 0)
const note = computed(() => store.notes.byId[props.noteId])
const childIds = computed(() => store.childrenIds[props.noteId] ?? [])
const isExpanded = computed(() => Boolean(store.expanded[props.noteId]))
const active = computed(() => route.params.id === props.noteId)

function open (): void {
  void router.push({ name: 'note', params: { id: props.noteId } })
}

function toggle (): void {
  void store.toggleExpanded(props.noteId)
}

function loadMore (): void {
  void store.loadChildren(props.noteId, true)
}

function togglePin (): void {
  if (!note.value) return
  void store.setPinned(note.value.id, !note.value.is_pinned)
}
</script>

<style lang="scss" scoped>
.notes-tree-node__loading {
  display: flex;
  justify-content: center;
  padding: 8px 0;
}
</style>
