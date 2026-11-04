<template>
  <div class="notes-browser">
    <div class="notes-browser__toolbar">
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="add"
        label="Новая заметка"
        @click="emit('create-root')"
      />
      <q-input
        v-model="searchInput"
        dense
        outlined
        clearable
        debounce="400"
        placeholder="Поиск по тексту"
        @update:model-value="onSearch"
      >
        <template #prepend>
          <q-icon name="search" />
        </template>
      </q-input>
    </div>

    <div
      v-if="store.isSearchLoading && !store.searchIds.length"
      class="notes-browser__state"
    >
      <q-spinner
        color="primary"
        size="32px"
      />
    </div>

    <template v-else-if="store.isSearchActive">
      <p class="notes-browser__caption">Результаты</p>
      <NoteRow
        v-for="note in searchNotes"
        :key="note.id"
        :note="note"
        :active="route.params.id === note.id"
        @open="open(note.id)"
        @create-child="emit('create-child', note.id)"
        @move="emit('move', note.id)"
        @pin="pin(note)"
        @remove="emit('remove', note.id)"
      />
      <NotesLoadMore
        :enabled="store.searchHasMore"
        :loading="store.isSearchLoadingMore"
        @load="loadMoreSearch"
      />
      <AppNoResultsPlug
        v-if="!store.searchIds.length && !store.isSearchLoading"
        title="Ничего не найдено"
        body="По этому тексту заметок нет"
      />
    </template>

    <div
      v-else-if="!store.listReady || (store.isRootsLoading && !store.rootIds.length)"
      class="notes-browser__state"
    >
      <q-spinner
        color="primary"
        size="32px"
      />
    </div>

    <template v-else>
      <NotesPinnedList
        @create-child="emit('create-child', $event)"
        @move="emit('move', $event)"
        @remove="emit('remove', $event)"
      />

      <AppNoResultsPlug
        v-if="!store.rootIds.length && !store.pinnedIds.length"
        title="Заметок пока нет"
        body="Создайте первую заметку"
      />

      <template v-if="store.rootIds.length">
        <p class="notes-browser__caption">Все заметки</p>
        <NotesTreeNode
          v-for="id in store.rootIds"
          :key="id"
          :note-id="id"
          @create-child="emit('create-child', $event)"
          @move="emit('move', $event)"
          @remove="emit('remove', $event)"
        />
        <NotesLoadMore
          :enabled="store.rootHasMore"
          :loading="store.isRootsLoadingMore"
          @load="loadMoreRoots"
        />
      </template>
    </template>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { INote } from 'src/types'
import { useNoteStore } from 'src/stores/modules/noteStore'
import NoteRow from 'src/components/client/Notes/NoteRow.vue'
import NotesTreeNode from 'src/components/client/Notes/NotesTreeNode.vue'
import NotesPinnedList from 'src/components/client/Notes/NotesPinnedList.vue'
import NotesLoadMore from 'src/components/client/Notes/NotesLoadMore.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const emit = defineEmits<{
  'create-root': []
  'create-child': [id: string]
  move: [id: string]
  remove: [id: string]
}>()

const store = useNoteStore()
const route = useRoute()
const router = useRouter()
const searchInput = ref('')

const searchNotes = computed(() => store.searchIds
  .map(id => store.notes.byId[id])
  .filter((note): note is INote => Boolean(note)))

function onSearch (value: string | number | null): void {
  void store.search(String(value ?? ''))
}

function loadMoreSearch (): void {
  void store.search(store.searchText, true)
}

function loadMoreRoots (): void {
  void store.loadRoots(true)
}

function pin (note: INote): void {
  void store.setPinned(note.id, !note.is_pinned)
}

function open (id: string): void {
  void router.push({ name: 'note', params: { id } })
}
</script>

<style lang="scss" scoped>
.notes-browser {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 12px;

  &__toolbar {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__caption {
    margin: 8px 8px 0;
    font-size: 13px;
    font-weight: 600;
    color: #5c6278;
  }

  &__state {
    display: flex;
    justify-content: center;
    padding: 32px 0;
  }
}
</style>
