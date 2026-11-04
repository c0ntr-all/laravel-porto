<template>
  <div
    class="notes-page"
    :class="{ 'notes-page--narrow': narrow }"
  >
    <section
      v-show="showList"
      class="notes-column"
    >
      <NotesBrowser
        @create-root="openCreate(null)"
        @create-child="openCreate"
        @move="openMove"
        @remove="confirmRemove"
      />
    </section>

    <section
      v-show="showEditor"
      class="notes-column notes-column--editor"
    >
      <div
        v-if="narrow && noteId"
        class="notes-page__back"
      >
        <q-btn
          flat
          no-caps
          icon="arrow_back"
          label="К списку"
          color="primary"
          @click="backToList"
        />
      </div>

      <div
        v-if="noteId && store.isCurrentLoading && !currentNote"
        class="notes-page__state"
      >
        <q-spinner
          color="primary"
          size="36px"
        />
      </div>

      <NoteEditor
        v-else-if="currentNote"
        :note="currentNote"
        :saving="store.isSaving"
        :conflict-message="store.conflictMessage"
        :generation="editorGeneration"
        @save="onSave"
        @reload="reloadFromServer"
        @pin="togglePin"
        @create-child="openCreate(currentNote.id)"
        @move="openMove(currentNote.id)"
        @remove="confirmRemove(currentNote.id)"
        @create-unresolved="createUnresolved"
      />

      <div
        v-else-if="noteId"
        class="notes-page__state"
      >
        <AppNoResultsPlug
          title="Заметка не открылась"
          body="Обновите список и попробуйте ещё раз"
        />
      </div>

      <div
        v-else
        class="notes-page__state"
      >
        <AppNoResultsPlug
          title="Заметка не выбрана"
          body="Выберите заметку слева или создайте новую"
        />
      </div>
    </section>

    <q-dialog v-model="createOpen">
      <q-card class="notes-dialog">
        <q-card-section class="text-h6">
          {{ createParentId ? 'Дочерняя заметка' : 'Новая заметка' }}
        </q-card-section>
        <q-card-section>
          <q-input
            v-model="createName"
            autofocus
            outlined
            dense
            label="Название"
            @keyup.enter="submitCreate"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn
            flat
            no-caps
            label="Отмена"
            @click="createOpen = false"
          />
          <q-btn
            unelevated
            no-caps
            color="primary"
            label="Создать"
            :loading="isCreating"
            @click="submitCreate"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="moveOpen">
      <q-card class="notes-dialog">
        <q-card-section class="text-h6">Переместить</q-card-section>
        <q-card-section class="q-gutter-sm">
          <q-btn
            v-if="moveSource?.parent_id"
            outline
            no-caps
            color="primary"
            label="Сделать корневой"
            :loading="isMoving"
            @click="submitMove(null)"
          />
          <q-input
            v-model="moveQuery"
            outlined
            dense
            debounce="300"
            clearable
            label="Найти родительскую заметку"
          />
          <q-spinner
            v-if="isMoveSearching"
            color="primary"
            size="20px"
          />
          <q-list
            v-else
            bordered
            separator
          >
            <q-item
              v-for="item in moveResults"
              :key="item.id"
              clickable
              @click="submitMove(Number(item.id))"
            >
              <q-item-section>
                <q-item-label>{{ item.name }}</q-item-label>
                <q-item-label
                  v-if="item.excerpt"
                  caption
                >
                  {{ item.excerpt }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn
            flat
            no-caps
            label="Закрыть"
            @click="moveOpen = false"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Dialog, Notify, useQuasar } from 'quasar'
import { INote, INoteCreatePayload, INoteUpdatePayload } from 'src/types'
import { useNoteStore } from 'src/stores/modules/noteStore'
import NotesBrowser from 'src/components/client/Notes/NotesBrowser.vue'
import NoteEditor from 'src/components/client/Notes/NoteEditor.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const store = useNoteStore()
const route = useRoute()
const router = useRouter()
const $q = useQuasar()

const editorGeneration = ref(0)
const createOpen = ref(false)
const createName = ref('')
const createParentId = ref<string | null>(null)
const isCreating = ref(false)
const moveOpen = ref(false)
const moveNoteId = ref<string | null>(null)
const moveQuery = ref('')
const moveResults = ref<INote[]>([])
const isMoving = ref(false)
const isMoveSearching = ref(false)
let moveSearchToken = 0

const narrow = computed(() => $q.screen.lt.md)
const noteId = computed(() => {
  const raw = route.params.id
  return typeof raw === 'string' ? raw : null
})
const showList = computed(() => !narrow.value || !noteId.value)
const showEditor = computed(() => !narrow.value || Boolean(noteId.value))
const currentNote = computed(() => {
  const note = store.current
  return note && note.id === noteId.value ? note : null
})
const moveSource = computed(() => {
  if (!moveNoteId.value) return null
  return store.notes.byId[moveNoteId.value] ?? null
})

watch(noteId, (id) => {
  if (!id) {
    store.closeNote()
    return
  }

  void store.openNote(id)
}, { immediate: true })

watch(moveQuery, (value) => {
  void searchMoveTarget(String(value ?? ''))
})

onMounted(() => {
  void store.loadRoots()
  void store.loadPinned()
})

function openCreate (parentId: string | null): void {
  createParentId.value = parentId
  createName.value = ''
  createOpen.value = true
}

async function submitCreate (): Promise<void> {
  const name = createName.value.trim()
  if (!name || isCreating.value) return

  isCreating.value = true
  const payload: INoteCreatePayload = { name }
  if (createParentId.value) payload.parent_id = Number(createParentId.value)

  const created = await store.createNote(payload)
  isCreating.value = false
  if (!created) return

  createOpen.value = false
  await router.push({ name: 'note', params: { id: created.id } })
}

function openMove (id: string): void {
  moveNoteId.value = id
  moveQuery.value = ''
  moveResults.value = []
  moveOpen.value = true
}

async function searchMoveTarget (raw: string): Promise<void> {
  const term = raw.trim()
  const token = ++moveSearchToken

  if (!term) {
    moveResults.value = []
    isMoveSearching.value = false
    return
  }

  isMoveSearching.value = true
  const items = await store.findNotes(term)
  if (token !== moveSearchToken) return

  const source = moveSource.value
  moveResults.value = items.filter(item => item.id !== moveNoteId.value && item.id !== source?.parent_id)
  isMoveSearching.value = false
}

async function submitMove (parentId: number | null): Promise<void> {
  if (!moveNoteId.value || isMoving.value) return

  isMoving.value = true
  const moved = await store.moveNote(moveNoteId.value, parentId)
  isMoving.value = false
  if (moved) moveOpen.value = false
}

function confirmRemove (id: string): void {
  const note = store.notes.byId[id]
  Dialog.create({
    title: 'Удалить заметку?',
    message: note
      ? `«${note.name}» будет удалена. Вложенные заметки поднимутся на уровень выше.`
      : 'Заметка будет удалена. Вложенные заметки поднимутся на уровень выше.',
    cancel: { label: 'Отмена', flat: true },
    ok: { label: 'Удалить', color: 'negative', flat: true },
    persistent: true
  }).onOk(() => {
    void removeNote(id)
  })
}

async function removeNote (id: string): Promise<void> {
  const ok = await store.removeNote(id)
  if (ok && noteId.value === id) {
    await router.push({ name: 'notes' })
  }
}

async function onSave (payload: INoteUpdatePayload): Promise<void> {
  if (!noteId.value) return
  const saved = await store.saveNote(noteId.value, payload)
  if (saved) editorGeneration.value += 1
}

async function reloadFromServer (): Promise<void> {
  if (!noteId.value) return
  const note = await store.openNote(noteId.value)
  if (note) editorGeneration.value += 1
}

function togglePin (): void {
  if (!currentNote.value) return
  void store.setPinned(currentNote.value.id, !currentNote.value.is_pinned)
}

async function createUnresolved (label: string): Promise<void> {
  const created = await store.createNote({ name: label })
  if (!created) return

  Notify.create({
    type: 'positive',
    message: `Заметка «${created.name}» создана. Ссылка появится после сохранения этой заметки.`
  })
}

function backToList (): void {
  void router.push({ name: 'notes' })
}
</script>

<style lang="scss" scoped>
.notes-page {
  display: grid;
  grid-template-columns: minmax(280px, 380px) minmax(0, 1fr);
  gap: 16px;
  align-items: stretch;
  min-height: calc(100vh - 196px);

  &--narrow {
    grid-template-columns: minmax(0, 1fr);
  }
}

.notes-column {
  background: #fff;
  border-radius: 16px;
  min-height: 480px;
  max-height: calc(100vh - 196px);
  overflow: auto;

  .notes-page--narrow & {
    max-height: none;
  }
}

.notes-page__back {
  padding: 8px 8px 0;
}

.notes-page__state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 280px;
}

.notes-dialog {
  width: 100%;
  max-width: 440px;
}
</style>
