<template>
  <q-dialog
    v-model="show"
    persistent
  >
    <q-card class="note-picker">
      <header class="note-picker__header">
        <h2 class="note-picker__title">
          Выбрать заметку
        </h2>
        <q-btn
          flat
          round
          dense
          icon="close"
          aria-label="Закрыть"
          @click="cancel"
        />
      </header>

      <q-separator />

      <div class="note-picker__toolbar">
        <q-input
          v-model="search"
          dense
          outlined
          clearable
          placeholder="Поиск по тексту"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>

      <div class="note-picker__body">
        <div v-if="isLoading && !notes.length" class="note-picker__state">
          <q-spinner-dots color="primary" size="36px" />
        </div>

        <div v-else-if="error" class="note-picker__state">
          <q-icon name="cloud_off" size="36px" />
          <div>{{ error }}</div>
          <q-btn
            flat
            no-caps
            color="primary"
            label="Повторить"
            @click="load"
          />
        </div>

        <div v-else-if="!notes.length" class="note-picker__state">
          <q-icon name="sticky_note_2" size="36px" />
          <div>{{ search.trim() ? 'Ничего не найдено' : 'Заметок пока нет' }}</div>
        </div>

        <div v-else class="note-picker__list">
          <button
            v-for="note in notes"
            :key="note.id"
            type="button"
            class="note-row"
            :class="{ 'note-row--selected': selectedId === note.id }"
            :aria-pressed="selectedId === note.id"
            @click="select(note)"
          >
            <span class="note-row__icon">
              <q-icon name="sticky_note_2" size="20px" />
            </span>
            <span class="note-row__text">
              <span class="note-row__name ellipsis">{{ note.name || 'Без названия' }}</span>
              <span
                v-if="note.excerpt"
                class="note-row__excerpt ellipsis"
              >{{ note.excerpt }}</span>
            </span>
            <q-icon
              v-if="selectedId === note.id"
              name="check_circle"
              color="positive"
              size="20px"
            />
          </button>
        </div>
      </div>

      <q-separator />

      <footer class="note-picker__footer">
        <q-btn
          flat
          no-caps
          color="grey-8"
          label="Отмена"
          @click="cancel"
        />
        <q-btn
          unelevated
          no-caps
          color="primary"
          label="Выбрать"
          :disable="!selected"
          @click="confirm"
        />
      </footer>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, onUnmounted, ref, watch } from 'vue'
import { noteApi } from 'src/api/requests/noteApi'
import { mapNotesResponse } from 'src/api/mappers/Notes/note.mapper'
import { IPostNoteSubject } from 'src/types'
import { INote } from 'src/types/Notes/note'
import { handleApiError } from 'src/utils/jsonapi'

const emit = defineEmits<{
  select: [note: IPostNoteSubject]
}>()

const show = defineModel<boolean>({ default: false })

const search = ref('')
const notes = ref<INote[]>([])
const selected = ref<IPostNoteSubject | null>(null)
const isLoading = ref(false)
const error = ref<string | null>(null)
let requestId = 0
let searchTimer: ReturnType<typeof setTimeout> | null = null

const selectedId = computed(() => selected.value?.id ?? null)

watch(show, isOpen => {
  if (!isOpen) {
    return
  }

  selected.value = null
  error.value = null
  if (search.value !== '') {
    search.value = ''
    return
  }

  void load()
})

watch(search, () => {
  if (!show.value) {
    return
  }

  if (searchTimer) {
    clearTimeout(searchTimer)
  }

  searchTimer = setTimeout(() => {
    void load()
  }, 300)
})

onUnmounted(() => {
  if (searchTimer) {
    clearTimeout(searchTimer)
  }
})

async function load () {
  const currentRequest = ++requestId
  isLoading.value = true
  error.value = null

  try {
    const response = await noteApi.list({
      text: search.value.trim() || undefined,
      perPage: 30,
      sort: '-updated_at'
    })

    if (currentRequest !== requestId) {
      return
    }

    notes.value = mapNotesResponse(response)
  } catch (err) {
    if (currentRequest !== requestId) {
      return
    }

    notes.value = []
    error.value = 'Не удалось загрузить заметки'
    handleApiError(err)
  } finally {
    if (currentRequest === requestId) {
      isLoading.value = false
    }
  }
}

function select (note: INote) {
  selected.value = {
    id: note.id,
    name: note.name,
    excerpt: note.excerpt || null
  }
}

function cancel () {
  show.value = false
}

function confirm () {
  if (!selected.value) {
    return
  }

  show.value = false
  emit('select', selected.value)
}
</script>

<style lang="scss" scoped>
.note-picker {
  display: flex;
  flex-direction: column;
  width: min(560px, 94vw);
  height: min(680px, 88vh);
  max-height: 88vh;
  border-radius: 16px;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex: none;
    padding: 14px 8px 14px 18px;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #282f53;
  }

  &__toolbar {
    flex: none;
    padding: 12px 16px 0;
  }

  &__body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 12px;
  }

  &__state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 220px;
    color: #777a8f;
    text-align: center;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  &__footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    flex: none;
    padding: 12px 16px;
  }
}

.note-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  background: #fff;
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;

  &:hover {
    border-color: $primary;
    background: rgba(108, 95, 252, 0.04);
  }

  &--selected {
    border-color: $primary;
    background: rgba(108, 95, 252, 0.06);
  }

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex: none;
    border-radius: 10px;
    background: rgba(242, 192, 55, 0.18);
    color: #c48a12;
  }

  &__text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1;
  }

  &__name {
    font-weight: 600;
    color: #282f53;
  }

  &__excerpt {
    color: #777a8f;
    font-size: 13px;
  }
}
</style>
