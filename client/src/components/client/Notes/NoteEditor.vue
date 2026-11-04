<template>
  <div class="note-editor">
    <div class="note-editor__bar">
      <q-btn
        flat
        round
        dense
        icon="push_pin"
        :color="note.is_pinned ? 'primary' : 'grey-7'"
        @click="emit('pin')"
      >
        <q-tooltip>{{ note.is_pinned ? 'Открепить' : 'Закрепить' }}</q-tooltip>
      </q-btn>
      <q-btn
        flat
        round
        dense
        icon="subdirectory_arrow_right"
        color="grey-7"
        @click="emit('create-child')"
      >
        <q-tooltip>Дочерняя заметка</q-tooltip>
      </q-btn>
      <q-btn
        flat
        round
        dense
        icon="drive_file_move"
        color="grey-7"
        @click="emit('move')"
      >
        <q-tooltip>Переместить</q-tooltip>
      </q-btn>
      <q-space />
      <q-btn
        v-if="!editing"
        flat
        no-caps
        color="primary"
        label="Редактировать"
        @click="editing = true"
      />
      <q-btn
        v-else
        flat
        no-caps
        color="grey-8"
        label="К чтению"
        :disable="dirty"
        @click="editing = false"
      />
      <q-btn
        v-if="editing"
        unelevated
        no-caps
        color="primary"
        label="Сохранить"
        :loading="saving"
        :disable="!dirty"
        @click="submit"
      />
      <q-btn
        flat
        round
        dense
        icon="delete"
        color="negative"
        @click="emit('remove')"
      >
        <q-tooltip>Удалить</q-tooltip>
      </q-btn>
    </div>

    <q-banner
      v-if="conflictMessage"
      class="note-editor__conflict"
      rounded
    >
      {{ conflictMessage }}
      <template #action>
        <q-btn
          flat
          no-caps
          color="primary"
          label="Загрузить версию с сервера"
          @click="emit('reload')"
        />
      </template>
    </q-banner>

    <div
      v-if="editing"
      class="note-editor__form"
    >
      <q-input
        v-model="draftName"
        label="Название"
        outlined
        dense
        :error="Boolean(nameError)"
        :error-message="nameError"
      />
      <q-editor
        v-model="draftContent"
        min-height="12rem"
        content-class="note-editor__content"
      />
    </div>

    <div
      v-else
      class="note-editor__read"
    >
      <h2 class="note-editor__title">{{ note.name }}</h2>
      <NoteContentView
        :content="note.content"
        :wiki-links="note.wiki_links"
        @create-unresolved="emit('create-unresolved', $event)"
      />
    </div>

    <NoteLinksPanel
      :note="note"
      @create-unresolved="emit('create-unresolved', $event)"
    />
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { INote, INoteUpdatePayload } from 'src/types'
import NoteContentView from 'src/components/client/Notes/NoteContentView.vue'
import NoteLinksPanel from 'src/components/client/Notes/NoteLinksPanel.vue'

const props = defineProps<{
  note: INote
  saving: boolean
  conflictMessage: string | null
  generation: number
}>()

const emit = defineEmits<{
  save: [payload: INoteUpdatePayload]
  reload: []
  pin: []
  'create-child': []
  move: []
  remove: []
  'create-unresolved': [label: string]
}>()

const draftName = ref('')
const draftContent = ref('')
const editing = ref(false)
const nameError = ref('')

const dirty = computed(() => {
  const name = draftName.value.trim()
  return name !== props.note.name || !sameContent(props.note.content, draftContent.value)
})

watch(() => props.note.id, () => {
  applyServer(!props.note.content)
}, { immediate: true })

watch(() => props.generation, () => {
  applyServer(false)
})

function applyServer (preferEdit: boolean): void {
  draftName.value = props.note.name
  draftContent.value = props.note.content ?? ''
  nameError.value = ''
  editing.value = preferEdit
}

function plainText (value: string | null): string {
  return (value ?? '')
    .replace(/<br\s*\/?>/gi, '')
    .replace(/&nbsp;/gi, '')
    .replace(/<[^>]+>/g, '')
    .trim()
}

function sameContent (left: string | null, right: string): boolean {
  if ((left ?? '') === right) return true
  return plainText(left) === '' && plainText(right) === ''
}

function submit (): void {
  const name = draftName.value.trim()
  if (!name) {
    nameError.value = 'Введите название'
    return
  }

  nameError.value = ''
  const payload: INoteUpdatePayload = {
    updated_at: props.note.updated_at
  }

  if (name !== props.note.name) payload.name = name
  if (!sameContent(props.note.content, draftContent.value)) payload.content = draftContent.value
  if (payload.name === undefined && payload.content === undefined) return

  emit('save', payload)
}
</script>

<style lang="scss" scoped>
.note-editor {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 16px;

  &__bar {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  &__conflict {
    background: #fff4e5;
    color: #6b4a16;
  }

  &__form {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  &__title {
    margin: 0 0 12px;
    font-size: 28px;
    line-height: 1.2;
    color: #282f53;
  }
}
</style>
