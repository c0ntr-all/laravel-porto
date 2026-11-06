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
      <div
        ref="frameRef"
        class="note-editor__frame"
      >
        <q-editor
          ref="editorRef"
          v-model="draftContent"
          min-height="12rem"
          content-class="note-editor__content"
          :toolbar="editorToolbar"
          :definitions="editorDefinitions"
          @focus="bindEditorChrome"
        />
        <q-btn
          v-if="tableControls"
          class="note-editor__table-plus note-editor__table-plus--col"
          :style="columnPlusStyle"
          round
          dense
          unelevated
          color="primary"
          icon="add"
          size="sm"
          aria-label="Добавить столбец"
          @mousedown.prevent
          @mouseenter="holdControls"
          @mouseleave="releaseControls"
          @click="addColumn"
        >
          <q-tooltip>Добавить столбец</q-tooltip>
        </q-btn>
        <q-btn
          v-if="tableControls"
          class="note-editor__table-plus note-editor__table-plus--row"
          :style="rowPlusStyle"
          round
          dense
          unelevated
          color="primary"
          icon="add"
          size="sm"
          aria-label="Добавить строку"
          @mousedown.prevent
          @mouseenter="holdControls"
          @mouseleave="releaseControls"
          @click="addRow"
        >
          <q-tooltip>Добавить строку</q-tooltip>
        </q-btn>
        <input
          ref="imageInput"
          class="note-editor__file"
          type="file"
          accept="image/*"
          multiple
          @change="onImages"
        >
      </div>
      <p
        v-if="imageUploading"
        class="note-editor__upload"
      >
        Загрузка изображения…
      </p>
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
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import type { QEditor, QEditorCommand } from 'quasar'
import { galleryApi } from 'src/api/requests/galleryApi'
import { mapGalleryAlbumsResponse, mapGalleryMediaUploadResponse } from 'src/api/mappers/gallery.mapper'
import { INote, INoteUpdatePayload } from 'src/types'
import { handleApiError } from 'src/utils/jsonapi'
import { galleryUploadUrl, resolveMediaUrl } from 'src/utils/gallery'
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

type CalloutTone = 'red' | 'yellow' | 'green' | 'blue'

const CALLOUT_CLASS: Record<CalloutTone, string> = {
  red: 'note-callout--red',
  yellow: 'note-callout--yellow',
  green: 'note-callout--green',
  blue: 'note-callout--blue'
}

const editorToolbar = [
  ['left', 'center', 'right', 'justify'],
  ['bold', 'italic', 'underline', 'strike'],
  ['unordered', 'ordered'],
  ['calloutRed', 'calloutYellow', 'calloutGreen', 'calloutBlue'],
  ['image', 'table'],
  ['undo', 'redo']
]

const EXIT_BLOCK_SELECTOR = '.note-callout, blockquote, pre'

interface TableBox {
  top: number
  left: number
  width: number
  height: number
}

const draftName = ref('')
const draftContent = ref('')
const editing = ref(false)
const nameError = ref('')
const imageUploading = ref(false)
const editorRef = ref<QEditor | null>(null)
const frameRef = ref<HTMLElement | null>(null)
const imageInput = ref<HTMLInputElement | null>(null)
const tableControls = ref<TableBox | null>(null)

let chromeContent: HTMLElement | null = null
let activeTable: HTMLTableElement | null = null
let hoveredTable: HTMLTableElement | null = null
let pointerOnPlus = false
let hideTimer: ReturnType<typeof setTimeout> | null = null

const columnPlusStyle = computed(() => {
  const box = tableControls.value
  if (!box) return undefined
  return {
    top: `${box.top + box.height / 2}px`,
    left: `${box.left + box.width}px`
  }
})

const rowPlusStyle = computed(() => {
  const box = tableControls.value
  if (!box) return undefined
  return {
    top: `${box.top + box.height}px`,
    left: `${box.left + box.width / 2}px`
  }
})

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

watch(editing, (isEditing) => {
  if (!isEditing) {
    unbindEditorChrome()
    tableControls.value = null
    return
  }
  nextTick(bindEditorChrome)
}, { immediate: true })

onBeforeUnmount(unbindEditorChrome)

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

function calloutButton (tip: string, color: string, tone: CalloutTone): QEditorCommand {
  return {
    tip,
    icon: 'priority_high',
    type: 'no-state',
    color,
    handler: () => insertCallout(tone)
  } as QEditorCommand
}

function insertCallout (tone: CalloutTone): void {
  insertBlock(
    `<div data-note-insert="1" class="note-callout ${CALLOUT_CLASS[tone]}"><span class="note-callout__mark">!</span><div><br></div></div>`,
    'inside',
    'div'
  )
}

function insertTable (): void {
  insertBlock(
    '<table data-note-insert="1" class="note-table"><tbody><tr><td><br></td><td><br></td></tr><tr><td><br></td><td><br></td></tr></tbody></table>',
    'inside',
    'td'
  )
}

function insertBlock (html: string, where: 'inside' | 'after', caretSelector = ''): void {
  const editor = editorRef.value
  const content = editor?.getContentEl()
  if (!editor || !(content instanceof HTMLElement)) return

  editor.runCmd('insertHTML', html)
  const inserted = content.querySelector('[data-note-insert]')
  if (!(inserted instanceof HTMLElement)) return

  inserted.removeAttribute('data-note-insert')
  if (where === 'after') {
    placeCaretAfterBlock(inserted)
  } else {
    ensureTrailingParagraph(inserted)
    const target = inserted.querySelector(caretSelector)
    if (target instanceof HTMLElement) placeCaretAtStart(target)
  }
  syncEditor(content)
  refreshTableControls()
}

function pickImages (): void {
  if (imageUploading.value) return
  imageInput.value?.click()
}

async function onImages (event: Event): Promise<void> {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files ?? []).filter(file => file.type.startsWith('image/'))
  input.value = ''
  if (!files.length) return

  imageUploading.value = true

  try {
    const albumId = await resolveUploadAlbumId()

    for (const file of files) {
      const response = await galleryApi.upload(
        galleryUploadUrl(albumId, 'photo', 'device'),
        file,
        () => undefined
      )
      const [image] = mapGalleryMediaUploadResponse(response)
      const src = resolveMediaUrl(image?.original || image?.preview_thumb_path)
      if (!src) {
        throw new Error('Сервер не вернул адрес изображения')
      }

      insertBlock(
        `<img data-note-insert="1" src="${escapeAttr(src)}" alt="${escapeAttr(imageAlt(file.name))}">`,
        'after'
      )
    }
  } catch (error) {
    handleApiError(error)
  } finally {
    imageUploading.value = false
  }
}

async function resolveUploadAlbumId (): Promise<string> {
  const response = await galleryApi.getAlbums()
  const album = mapGalleryAlbumsResponse(response).find(item => item.system_code === 'upload')
  if (!album) {
    throw new Error('Альбом Upload не найден')
  }

  return album.id
}

function imageAlt (name: string): string {
  return name.replace(/\.[^.]+$/, '').trim().slice(0, 120)
}

function escapeAttr (value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
}

function onEditorKeydown (event: KeyboardEvent): void {
  if (event.key !== 'Enter' || event.shiftKey || event.altKey || event.ctrlKey || event.metaKey || event.isComposing) return
  const content = chromeContent
  if (!content) return
  const block = exitBlock(content)
  if (!block) return

  event.preventDefault()
  event.stopPropagation()
  placeCaretAtStart(paragraphAfter(block))
  syncEditor(content)
}

function exitBlock (content: HTMLElement): HTMLElement | null {
  const node = window.getSelection()?.anchorNode
  if (!node || !content.contains(node)) return null
  const element = node instanceof Element ? node : node.parentElement
  const block = element?.closest(EXIT_BLOCK_SELECTOR)
  if (!(block instanceof HTMLElement) || !content.contains(block)) return null
  return block
}

function paragraphAfter (block: HTMLElement): HTMLElement {
  const next = block.nextElementSibling
  if (next instanceof HTMLElement && isBlankParagraph(next)) return next
  const paragraph = document.createElement('div')
  paragraph.appendChild(document.createElement('br'))
  block.after(paragraph)
  return paragraph
}

function isBlankParagraph (element: HTMLElement): boolean {
  if (element.tagName !== 'DIV' && element.tagName !== 'P') return false
  if (element.matches(EXIT_BLOCK_SELECTOR)) return false
  if (element.querySelector('img, table, .note-callout, blockquote, pre')) return false
  return (element.textContent ?? '').replace(/\u00a0/g, '').trim() === ''
}

function placeCaretAtStart (element: HTMLElement): void {
  const range = document.createRange()
  range.selectNodeContents(element)
  range.collapse(true)
  const selection = window.getSelection()
  if (!selection) return
  selection.removeAllRanges()
  selection.addRange(range)
}

function placeCaretAfter (node: Node): void {
  const range = document.createRange()
  range.setStartAfter(node)
  range.collapse(true)
  const selection = window.getSelection()
  if (!selection) return
  selection.removeAllRanges()
  selection.addRange(range)
}

function ensureTrailingParagraph (block: HTMLElement): HTMLElement | null {
  if (block.nextElementSibling) return null
  const paragraph = document.createElement('div')
  paragraph.appendChild(document.createElement('br'))
  block.after(paragraph)
  return paragraph
}

function placeCaretAfterBlock (block: HTMLElement): void {
  const created = ensureTrailingParagraph(block)
  if (created) {
    placeCaretAtStart(created)
    return
  }
  const next = block.nextElementSibling
  if (next instanceof HTMLElement && isBlankParagraph(next)) {
    placeCaretAtStart(next)
    return
  }
  placeCaretAfter(block)
}

function syncEditor (content: HTMLElement): void {
  content.dispatchEvent(new InputEvent('input', { bubbles: true }))
}

function bindEditorChrome (): void {
  const content = editorRef.value?.getContentEl()
  if (!(content instanceof HTMLElement) || chromeContent === content) return
  unbindEditorChrome()
  chromeContent = content
  content.addEventListener('keydown', onEditorKeydown, true)
  content.addEventListener('keyup', refreshTableControls)
  content.addEventListener('mouseup', refreshTableControls)
  content.addEventListener('mouseover', onContentOver)
  content.addEventListener('mouseout', onContentOut)
  content.addEventListener('scroll', refreshTableControls)
  document.addEventListener('selectionchange', refreshTableControls)
  window.addEventListener('resize', refreshTableControls)
  window.addEventListener('scroll', refreshTableControls, true)
}

function unbindEditorChrome (): void {
  if (hideTimer) {
    clearTimeout(hideTimer)
    hideTimer = null
  }
  const content = chromeContent
  chromeContent = null
  activeTable = null
  hoveredTable = null
  pointerOnPlus = false
  if (!content) return
  content.removeEventListener('keydown', onEditorKeydown, true)
  content.removeEventListener('keyup', refreshTableControls)
  content.removeEventListener('mouseup', refreshTableControls)
  content.removeEventListener('mouseover', onContentOver)
  content.removeEventListener('mouseout', onContentOut)
  content.removeEventListener('scroll', refreshTableControls)
  document.removeEventListener('selectionchange', refreshTableControls)
  window.removeEventListener('resize', refreshTableControls)
  window.removeEventListener('scroll', refreshTableControls, true)
}

function onContentOver (event: MouseEvent): void {
  const target = event.target
  if (!(target instanceof Element) || !chromeContent) return
  const table = target.closest('table')
  if (!(table instanceof HTMLTableElement) || !chromeContent.contains(table)) return
  hoveredTable = table
  if (hideTimer) {
    clearTimeout(hideTimer)
    hideTimer = null
  }
  refreshTableControls()
}

function onContentOut (event: MouseEvent): void {
  const target = event.target
  if (!(target instanceof Element)) return
  const table = target.closest('table')
  if (!(table instanceof HTMLTableElement) || hoveredTable !== table) return
  const related = event.relatedTarget
  if (related instanceof Node && table.contains(related)) return
  scheduleHide()
}

function scheduleHide (): void {
  if (hideTimer) clearTimeout(hideTimer)
  hideTimer = setTimeout(() => {
    hideTimer = null
    if (pointerOnPlus) return
    hoveredTable = null
    refreshTableControls()
  }, 200)
}

function holdControls (): void {
  pointerOnPlus = true
  if (hideTimer) {
    clearTimeout(hideTimer)
    hideTimer = null
  }
}

function releaseControls (): void {
  pointerOnPlus = false
  scheduleHide()
}

function tableFromSelection (content: HTMLElement): HTMLTableElement | null {
  const node = window.getSelection()?.anchorNode
  if (!node || !content.contains(node)) return null
  const element = node instanceof Element ? node : node.parentElement
  const table = element?.closest('table')
  return table instanceof HTMLTableElement && content.contains(table) ? table : null
}

function refreshTableControls (): void {
  const content = chromeContent
  const frame = frameRef.value
  if (!content || !frame) {
    tableControls.value = null
    return
  }

  const selected = tableFromSelection(content)
  const hover = hoveredTable && content.contains(hoveredTable) ? hoveredTable : null
  const table = hover ?? selected
  if (!table) {
    if (!pointerOnPlus) {
      activeTable = null
      tableControls.value = null
    }
    return
  }

  activeTable = table
  const tableRect = table.getBoundingClientRect()
  const frameRect = frame.getBoundingClientRect()
  const next = {
    top: tableRect.top - frameRect.top,
    left: tableRect.left - frameRect.left,
    width: tableRect.width,
    height: tableRect.height
  }
  const current = tableControls.value
  if (
    current &&
    Math.abs(current.top - next.top) < 0.5 &&
    Math.abs(current.left - next.left) < 0.5 &&
    Math.abs(current.width - next.width) < 0.5 &&
    Math.abs(current.height - next.height) < 0.5
  ) return

  tableControls.value = next
}

function addColumn (): void {
  const table = activeTable
  const content = chromeContent
  if (!table || !content || !content.contains(table)) return

  const rows = Array.from(table.rows)
  if (!rows.length) {
    table.insertRow(-1).insertCell(-1).appendChild(document.createElement('br'))
  } else {
    rows.forEach(row => {
      row.insertCell(-1).appendChild(document.createElement('br'))
    })
  }
  syncEditor(content)
  refreshTableControls()
}

function addRow (): void {
  const table = activeTable
  const content = chromeContent
  if (!table || !content || !content.contains(table)) return

  const columns = Math.max(table.rows[0]?.cells.length ?? 0, 1)
  const row = table.insertRow(-1)
  for (let index = 0; index < columns; index += 1) {
    row.insertCell(-1).appendChild(document.createElement('br'))
  }
  syncEditor(content)
  refreshTableControls()
}

const editorDefinitions: Record<string, QEditorCommand> = {
  calloutRed: calloutButton('Красная панель', 'negative', 'red'),
  calloutYellow: calloutButton('Жёлтая панель', 'warning', 'yellow'),
  calloutGreen: calloutButton('Зелёная панель', 'positive', 'green'),
  calloutBlue: calloutButton('Синяя панель', 'info', 'blue'),
  image: {
    tip: 'Изображение',
    icon: 'image',
    type: 'no-state',
    disable: () => imageUploading.value,
    handler: pickImages
  },
  table: {
    tip: 'Таблица',
    icon: 'grid_on',
    type: 'no-state',
    handler: insertTable
  }
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

  &__frame {
    position: relative;
  }

  &__table-plus {
    position: absolute;
    z-index: 2;

    &--col {
      transform: translate(6px, -50%);
    }

    &--row {
      transform: translate(-50%, 6px);
    }
  }

  &__title {
    margin: 0 0 12px;
    font-size: 28px;
    line-height: 1.2;
    color: #282f53;
  }

  &__file {
    display: none;
  }

  &__upload {
    margin: 0;
    color: #777a8f;
    font-size: 13px;
  }

  :deep(.note-editor__content) {
    padding-right: 36px;
    padding-bottom: 36px;
  }

  :deep(.note-editor__content img) {
    max-width: 100%;
    height: auto;
  }

  :deep(.note-table) {
    width: 100%;
    margin: 0 0 0.8em;
    border-collapse: collapse;
    table-layout: fixed;

    td {
      border: 1px solid #d7dbe8;
      padding: 6px 8px;
      vertical-align: top;
      min-width: 48px;
    }
  }

  :deep(.note-callout) {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 0 0 0.8em;
    padding: 10px 12px;
    border-radius: 10px;
  }

  :deep(.note-callout__mark) {
    flex: none;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 22px;
    text-align: center;
  }

  :deep(.note-callout > div) {
    flex: 1;
    min-width: 0;
  }

  :deep(.note-callout--red) {
    background: #fdecea;
    color: #8a1f17;
  }

  :deep(.note-callout--red .note-callout__mark) {
    background: #e53935;
  }

  :deep(.note-callout--yellow) {
    background: #fff8e1;
    color: #8a5a00;
  }

  :deep(.note-callout--yellow .note-callout__mark) {
    background: #f9a825;
  }

  :deep(.note-callout--green) {
    background: #e8f6ee;
    color: #1b5e20;
  }

  :deep(.note-callout--green .note-callout__mark) {
    background: #43a047;
  }

  :deep(.note-callout--blue) {
    background: #e8f1fb;
    color: #0d47a1;
  }

  :deep(.note-callout--blue .note-callout__mark) {
    background: #1e88e5;
  }
}
</style>
