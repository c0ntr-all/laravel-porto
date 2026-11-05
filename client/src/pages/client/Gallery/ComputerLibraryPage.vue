<template>
  <div class="library">
    <div class="q-mb-md">
      <AppBackButton link="/gallery" text="Back to albums" />
    </div>

    <div class="library__head">
      <div>
        <h1 class="library__title">Folders on this computer</h1>
        <div v-if="listing?.path" class="library__path">{{ listing.path }}</div>
      </div>
      <div class="library__actions">
        <q-btn
          outline
          no-caps
          color="primary"
          icon="link"
          label="Relink missing"
          :loading="relinking"
          @click="relink"
        />
        <q-btn
          unelevated
          no-caps
          color="primary"
          icon="photo_album"
          label="Add to album"
          :disable="!selectedPaths.length"
          :loading="galleryStore.isUploading"
          @click="showAlbumDialog = true"
        />
      </div>
    </div>

    <q-breadcrumbs class="library__crumbs" gutter="sm">
      <q-breadcrumbs-el
        v-for="crumb in breadcrumbs"
        :key="crumb.path"
        :label="crumb.name"
        class="cursor-pointer"
        @click="openFolder(crumb.path)"
      />
    </q-breadcrumbs>

    <div v-if="listing?.folders.length" class="library__folders">
      <button
        v-for="folder in listing.folders"
        :key="folder.path"
        type="button"
        class="library__folder"
        @click="openFolder(folder.path)"
      >
        <q-icon name="folder" color="primary" size="20px" />
        <span>{{ folder.name }}</span>
      </button>
    </div>

    <div v-if="loading && !listing" class="library__state">Loading folder…</div>
    <div v-else-if="!files.length" class="library__state">No photos in this folder.</div>

    <div v-else ref="gridHost" class="library__grid-host">
      <q-virtual-scroll
        :items="rows"
        :virtual-scroll-item-size="rowHeight"
        class="library__scroll"
        v-slot="{ item: row }"
        @virtual-scroll="onVirtualScroll"
      >
        <div class="library__row" :style="{ height: `${rowHeight}px` }">
          <button
            v-for="file in row"
            :key="file.path"
            type="button"
            class="library__card"
            :class="{ 'library__card--selected': selected.has(file.path) }"
            @click="toggle(file.path)"
          >
            <img
              v-if="previewUrl(file)"
              class="library__thumb"
              :src="previewUrl(file)"
              :alt="file.name"
            >
            <div v-else class="library__placeholder">
              <q-icon name="image" size="28px" />
              <span>{{ file.name }}</span>
            </div>
            <div v-if="file.in_gallery" class="library__badge">In gallery</div>
            <div v-if="file.file_missing" class="library__badge library__badge--missing">File not found</div>
            <q-icon
              v-if="selected.has(file.path)"
              class="library__check"
              name="check_circle"
            />
          </button>
        </div>
      </q-virtual-scroll>
    </div>

    <div v-if="listing && listing.last_page > 1" class="library__pager">
      <q-pagination
        :model-value="listing.page"
        :max="listing.last_page"
        :max-pages="7"
        boundary-numbers
        direction-links
        color="primary"
        @update:model-value="changePage"
      />
    </div>

    <q-dialog v-model="showAlbumDialog">
      <q-card class="library__dialog">
        <q-card-section>
          <div class="text-h6">Add {{ selectedPaths.length }} to an album</div>
          <div class="library__dialog-hint">
            The original stays on this computer. The album keeps the path and a small preview.
          </div>
        </q-card-section>
        <q-card-section>
          <q-select
            v-model="targetAlbumId"
            :options="albumOptions"
            emit-value
            map-options
            outlined
            dense
            label="Album"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancel" v-close-popup />
          <q-btn
            unelevated
            no-caps
            color="primary"
            label="Add"
            :disable="!targetAlbumId"
            :loading="galleryStore.isUploading"
            @click="addToAlbum"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Notify } from 'quasar'
import { galleryApi } from 'src/api/requests/galleryApi'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { IGalleryLibraryFile, IGalleryLibraryListing } from 'src/types/gallery'
import { resolveMediaUrl } from 'src/utils/gallery'

const galleryStore = useGalleryStore()
const listing = ref<IGalleryLibraryListing | null>(null)
const loading = ref(false)
const relinking = ref(false)
const showAlbumDialog = ref(false)
const targetAlbumId = ref<string | null>(null)
const selected = ref(new Set<string>())
const previews = ref<Record<string, string>>({})
const asked = ref(new Set<string>())
const gridHost = ref<HTMLElement | null>(null)
const columns = ref(4)
const rowHeight = 188
const pageSize = 60
let pollTimer = 0
let requestToken = 0

const files = computed(() => listing.value?.files ?? [])
const breadcrumbs = computed(() => listing.value?.breadcrumbs ?? [])
const selectedPaths = computed(() => [...selected.value])
const albumOptions = computed(() => (
  galleryStore.albums
    .filter(album => !album.is_system)
    .map(album => ({ label: album.name, value: album.id }))
))

const rows = computed(() => {
  const size = Math.max(1, columns.value)
  const result: IGalleryLibraryFile[][] = []

  for (let index = 0; index < files.value.length; index += size) {
    result.push(files.value.slice(index, index + size))
  }

  return result
})

function previewUrl(file: IGalleryLibraryFile): string {
  return resolveMediaUrl(file.thumb_url || previews.value[file.path] || '')
}

function toggle(path: string): void {
  const next = new Set(selected.value)

  if (next.has(path)) {
    next.delete(path)
  } else {
    next.add(path)
  }

  selected.value = next
}

async function load(path?: string, page = 1): Promise<void> {
  const token = ++requestToken
  loading.value = true

  try {
    const next = await galleryApi.listComputerFolder(path, page, pageSize)

    if (token !== requestToken) {
      return
    }

    listing.value = next
    selected.value = new Set()
    previews.value = {}
    asked.value = new Set()
  } catch {
    Notify.create({ type: 'negative', message: 'Could not read this folder.' })
  } finally {
    if (token === requestToken) {
      loading.value = false
    }
  }
}

function openFolder(path: string): void {
  void load(path, 1)
}

function changePage(page: number): void {
  if (!listing.value) {
    return
  }

  void load(listing.value.path, page)
}

async function requestThumbs(batch: IGalleryLibraryFile[], force = false): Promise<void> {
  const pending = batch
    .filter(file => !file.thumb_url && !previews.value[file.path] && (force || !asked.value.has(file.path)))
    .slice(0, 30)

  if (!pending.length) {
    return
  }

  asked.value = new Set([...asked.value, ...pending.map(file => file.path)])

  try {
    const items = await galleryApi.queueComputerThumbs(pending.map(file => file.path))
    const next = { ...previews.value }

    for (const item of items) {
      if (item.status === 'ready' && item.url) {
        next[item.path] = item.url
      }
    }

    previews.value = next
  } catch {
    // The card keeps the file name until a later scroll retries the preview.
  }
}

function onVirtualScroll(details: { from: number; to: number }): void {
  const visible: IGalleryLibraryFile[] = []

  for (let index = details.from; index <= details.to; index += 1) {
    const row = rows.value[index]

    if (row) {
      visible.push(...row)
    }
  }

  void requestThumbs(visible)
}

async function pollPending(): Promise<void> {
  const pending = files.value.filter(file => (
    asked.value.has(file.path) && !file.thumb_url && !previews.value[file.path]
  ))

  if (!pending.length) {
    return
  }

  await requestThumbs(pending, true)
}

async function relink(): Promise<void> {
  if (!listing.value) {
    return
  }

  relinking.value = true

  try {
    const result = await galleryApi.relinkComputerFolder(listing.value.path)
    Notify.create({
      type: result.relinked ? 'positive' : 'warning',
      message: result.relinked
        ? `Matched ${result.relinked} file${result.relinked === 1 ? '' : 's'}.`
        : 'No missing files matched this folder.'
    })
    await load(listing.value.path, listing.value.page)
  } catch {
    Notify.create({ type: 'negative', message: 'Could not relink files in this folder.' })
  } finally {
    relinking.value = false
  }
}

async function addToAlbum(): Promise<void> {
  if (!targetAlbumId.value || !selectedPaths.value.length) {
    return
  }

  try {
    await galleryStore.uploadFromWindows(selectedPaths.value, targetAlbumId.value)
    showAlbumDialog.value = false
    selected.value = new Set()

    if (listing.value) {
      await load(listing.value.path, listing.value.page)
    }
  } catch {
    // The store already reports the upload error.
  }
}

function measure(): void {
  const width = gridHost.value?.clientWidth ?? window.innerWidth - 80
  columns.value = Math.max(2, Math.floor(width / 180))
}

watch(files, (items) => {
  void requestThumbs(items.slice(0, Math.max(columns.value * 2, 8)))
})

watch(showAlbumDialog, (open) => {
  if (open && !galleryStore.albums.length) {
    void galleryStore.getAlbums()
  }

  if (open && !targetAlbumId.value && albumOptions.value[0]) {
    targetAlbumId.value = albumOptions.value[0].value
  }
})

onMounted(() => {
  void galleryStore.getAlbums()
  void load()
  measure()
  window.addEventListener('resize', measure)
  pollTimer = window.setInterval(() => {
    void pollPending()
  }, 2000)
})

onUnmounted(() => {
  window.removeEventListener('resize', measure)
  window.clearInterval(pollTimer)
})
</script>

<style lang="scss" scoped>
.library {
  &__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 12px;
  }

  &__title {
    margin: 0;
    font-size: 28px;
    line-height: 1.2;
    color: #282f53;
  }

  &__path {
    margin-top: 4px;
    color: #777a8f;
    font-size: 13px;
  }

  &__actions {
    display: flex;
    gap: 8px;
  }

  &__crumbs {
    margin-bottom: 16px;
  }

  &__folders {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
  }

  &__folder {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border: 0;
    border-radius: 12px;
    background: #f6f6fa;
    color: #282f53;
    cursor: pointer;
  }

  &__state {
    padding: 32px 0;
    color: #9aa0b8;
    text-align: center;
  }

  &__grid-host {
    height: calc(100vh - 280px);
    min-height: 320px;
  }

  &__scroll {
    height: 100%;
  }

  &__row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
    padding-bottom: 12px;
  }

  &__card {
    position: relative;
    height: 168px;
    padding: 0;
    overflow: hidden;
    border: 0;
    border-radius: 12px;
    background: #ececf4;
    cursor: pointer;

    &--selected {
      box-shadow: inset 0 0 0 3px $primary;
    }
  }

  &__thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  &__placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 100%;
    padding: 12px;
    color: #777a8f;
    font-size: 12px;
    text-align: center;
    word-break: break-word;
  }

  &__badge {
    position: absolute;
    left: 8px;
    bottom: 8px;
    padding: 2px 6px;
    border-radius: 6px;
    background: rgba(18, 18, 18, 0.72);
    color: #fff;
    font-size: 11px;

    &--missing {
      background: rgba(193, 0, 21, 0.85);
    }
  }

  &__check {
    position: absolute;
    top: 8px;
    right: 8px;
    color: #fff;
    font-size: 22px;
    filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.4));
  }

  &__pager {
    display: flex;
    justify-content: center;
    margin-top: 16px;
  }

  &__dialog {
    width: 420px;
    max-width: 92vw;
  }

  &__dialog-hint {
    margin-top: 8px;
    color: #777a8f;
    font-size: 13px;
  }
}
</style>
