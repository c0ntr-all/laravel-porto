<template>
  <q-dialog v-model="show">
    <q-card class="album-dialog">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6">Edit album</div>
          <div class="text-caption text-grey-7">
            {{ album.parent_id ? 'Grouped under another album' : 'Standalone release' }}
          </div>
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row q-col-gutter-lg">
          <div class="col-12 col-sm-4">
            <div class="album-dialog__cover q-mb-sm">
              <q-img v-if="coverPreview" :src="coverPreview" ratio="1" />
              <div v-else class="album-dialog__placeholder">
                <q-icon name="album" size="64px" color="grey-5" />
              </div>
            </div>
            <q-file
              v-model="imageFile"
              label="Cover image"
              accept="image/*"
              outlined
              dense
              clearable
            />
          </div>
          <div class="col-12 col-sm-8 q-gutter-md">
            <q-input v-model="name" label="Name" outlined dense />
            <q-select
              v-model="albumTypeId"
              :options="typeOptions"
              label="Version"
              option-value="id"
              option-label="label"
              emit-value
              map-options
              outlined
              dense
              :loading="admin.isAlbumTypesLoading"
              hint="Album type: Studio, Live, EP, Demo…"
            />
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-input
                  v-model="edition"
                  label="Edition"
                  outlined
                  dense
                  clearable
                  hint="Free text: Remastered, Deluxe, Anniversary…"
                />
              </div>
              <div class="col-6">
                <q-input v-model="date" label="Release date" type="date" outlined dense />
              </div>
            </div>
            <q-select
              v-model="parentId"
              :options="parentSelectOptions"
              label="Group under original album"
              option-value="id"
              option-label="label"
              emit-value
              map-options
              use-input
              outlined
              dense
              clearable
              input-debounce="300"
              :loading="parentSearchLoading"
              hint="Same artist only. Leave empty for a standalone original"
              @filter="filterParents"
            />
            <q-select
              v-model="artistIds"
              :options="artistOptions"
              label="Artists"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              use-input
              use-chips
              multiple
              outlined
              dense
              input-debounce="300"
              @filter="filterArtists"
            />
            <q-input
              v-model="description"
              label="Description"
              type="textarea"
              autogrow
              outlined
              dense
            />
            <div class="text-subtitle2">Tags</div>
            <div
              v-if="tagsUnlocked"
              class="album-dialog__tags-note"
            >
              <q-icon name="warning_amber" size="18px" color="warning" />
              <span>Изменения применятся ко всем трекам этого альбома.</span>
            </div>
            <div
              class="album-dialog__tags"
              :class="{ 'is-locked': !tagsUnlocked }"
            >
              <div
                v-if="!tagsUnlocked"
                class="album-dialog__tags-banner"
                aria-hidden="true"
              >
                <q-icon name="lock" size="16px" />
                <span>Нажмите, чтобы изменить</span>
              </div>
              <MusicTagGroupSelect
                v-model="tagIds"
                :disable="!tagsUnlocked"
              />
              <button
                v-if="!tagsUnlocked"
                type="button"
                class="album-dialog__tags-shield"
                aria-label="Нажмите, чтобы изменить теги"
                @click="showTagsConfirm = true"
              />
            </div>
          </div>
        </div>

        <div v-if="discs.length" class="q-mt-lg">
          <div class="text-subtitle2 q-mb-xs">Discs / CDs</div>
          <div class="text-caption text-grey-7 q-mb-sm">
            Rename physical discs of this release. Track assignment stays unchanged.
          </div>
          <div class="q-gutter-sm">
            <div
              v-for="(disc, index) in discs"
              :key="disc.id"
              class="row q-col-gutter-sm items-start album-dialog__disc"
            >
              <div class="col-auto">
                <q-badge color="grey-8" class="album-dialog__disc-badge">
                  CD {{ disc.number }}
                </q-badge>
              </div>
              <div class="col">
                <q-input
                  v-model="discs[index].name"
                  :label="`Disc ${disc.number} name`"
                  outlined
                  dense
                  hide-bottom-space
                />
              </div>
              <div v-if="disc.tracks_count != null" class="col-auto self-center">
                <span class="text-caption text-grey-6">
                  {{ disc.tracks_count }} {{ disc.tracks_count === 1 ? 'track' : 'tracks' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="album.versions.length" class="q-mt-lg">
          <div class="text-subtitle2 q-mb-sm">Versions of this album</div>
          <q-list bordered separator dense>
            <q-item v-for="version in album.versions" :key="version.id">
              <q-item-section>
                {{ version.name }}
                <span v-if="version.album_type" class="text-deep-purple q-ml-xs">{{ version.album_type.label }}</span>
                <span v-if="version.edition" class="text-orange q-ml-xs"> · {{ version.edition }}</span>
              </q-item-section>
              <q-item-section side>{{ albumYear(version.date) }}</q-item-section>
            </q-item>
          </q-list>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-actions align="right">
        <q-btn flat no-caps v-close-popup>Cancel</q-btn>
        <q-btn
          color="primary"
          unelevated
          no-caps
          :loading="admin.isAlbumSaving"
          :disable="!name.trim() || !isDirty"
          @click="save"
        >
          Save
        </q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>

  <q-dialog v-model="showTagsConfirm" persistent>
    <q-card class="album-tags-confirm">
      <q-card-section class="row items-start no-wrap q-gutter-md">
        <q-avatar
          icon="warning_amber"
          color="warning"
          text-color="dark"
          font-size="22px"
        />
        <div class="album-tags-confirm__text">
          Изменение тегов у альбома применится ко всем трекам этого альбома! Продолжить?
        </div>
      </q-card-section>
      <q-card-actions align="right" class="q-px-md q-pb-md">
        <q-btn
          flat
          no-caps
          label="Нет"
          color="grey-8"
          autofocus
          v-close-popup
        />
        <q-btn
          unelevated
          no-caps
          label="Да"
          color="warning"
          text-color="dark"
          @click="unlockTags"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useMusicAdminStore } from 'src/stores/modules/musicAdminStore'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import MusicTagGroupSelect from 'src/components/admin/Music/MusicTagGroupSelect.vue'
import { albumYear } from 'src/utils/albumDate'
import { hasAlbumWritePatch, mapAlbumWritePatch } from 'src/api/mappers/Music/album.mapper'
import { IAlbum, IAlbumDiscFormState, IAlbumFormState, IArtistShort } from 'src/types'

const props = defineProps<{
  modelValue: boolean
  album: IAlbum
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: []
}>()

const admin = useMusicAdminStore()
const tagStore = useMusicTagStore()
const show = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})

const name = ref(props.album.name)
const description = ref(props.album.description ?? '')
const edition = ref(props.album.edition ?? '')
const albumTypeId = ref(props.album.album_type?.id ?? (props.album.album_type_id != null ? String(props.album.album_type_id) : null))
const date = ref(props.album.date ?? '')
const parentId = ref(props.album.parent_id)
const artistIds = ref(props.album.artists.map(artist => artist.id))
const tagIds = ref(props.album.tags.map(tag => tag.id))
const discs = ref<IAlbumDiscFormState[]>(
  props.album.discs.map(disc => ({
    id: disc.id,
    number: disc.number,
    name: disc.name,
    tracks_count: disc.tracks_count
  }))
)
const imageFile = ref<File | null>(null)
const tagsUnlocked = ref(false)
const showTagsConfirm = ref(false)
const artistOptions = ref<IArtistShort[]>([...props.album.artists])
const extraParents = ref<IAlbum[]>([])
const parentSearchLoading = ref(false)

const typeOptions = computed(() => admin.albumTypes)

const coverPreview = computed(() => {
  if (imageFile.value) {
    return URL.createObjectURL(imageFile.value)
  }

  return props.album.image || ''
})

const parentSelectOptions = computed(() => {
  const options = extraParents.value
    .filter(item => item.id !== props.album.id && !item.parent_id)
    .map(item => ({
      id: item.id,
      label: `${item.name}${item.artists[0] ? ` — ${item.artists[0].name}` : ''}`
    }))

  if (props.album.parent && !options.some(item => item.id === props.album.parent?.id)) {
    options.unshift({
      id: props.album.parent.id,
      label: `${props.album.parent.name} (current original)`
    })
  }

  return options
})

const hydrate = (album: IAlbum) => {
  name.value = album.name
  description.value = album.description ?? ''
  edition.value = album.edition ?? ''
  albumTypeId.value = album.album_type?.id ?? (album.album_type_id != null ? String(album.album_type_id) : null)
  date.value = album.date ?? ''
  parentId.value = album.parent_id
  artistIds.value = album.artists.map(artist => artist.id)
  tagIds.value = album.tags.map(tag => tag.id)
  discs.value = album.discs.map(disc => ({
    id: disc.id,
    number: disc.number,
    name: disc.name,
    tracks_count: disc.tracks_count
  }))
  imageFile.value = null
  tagsUnlocked.value = false
  showTagsConfirm.value = false
  artistOptions.value = [...album.artists]
  extraParents.value = []
}

watch(() => props.album, hydrate)

watch(show, open => {
  tagsUnlocked.value = false

  if (!open) {
    showTagsConfirm.value = false
  }
})

const unlockTags = () => {
  tagsUnlocked.value = true
  showTagsConfirm.value = false
}

const formState = computed((): IAlbumFormState => ({
  name: name.value,
  description: description.value,
  edition: edition.value,
  album_type_id: albumTypeId.value,
  date: date.value,
  parent_id: parentId.value,
  artist_ids: artistIds.value,
  tag_ids: tagIds.value,
  discs: discs.value.map(disc => ({
    id: disc.id,
    number: disc.number,
    name: disc.name
  })),
  image_file: imageFile.value
}))

const patch = computed(() => mapAlbumWritePatch(props.album, formState.value))
const isDirty = computed(() => hasAlbumWritePatch(patch.value))

const filterArtists = async (val: string, update: (fn: () => void) => void) => {
  const found = await admin.searchArtistOptions(val)
  update(() => {
    const merged = new Map<string, IArtistShort>()
    ;[...props.album.artists, ...found].forEach(artist => {
      merged.set(artist.id, { id: artist.id, name: artist.name })
    })
    artistOptions.value = [...merged.values()]
  })
}

const filterParents = async (val: string, update: (fn: () => void) => void) => {
  parentSearchLoading.value = true

  try {
    const found = await admin.searchAlbumOptions(val, artistIds.value)
    update(() => {
      extraParents.value = found
    })
  } catch {
    update(() => {
      extraParents.value = []
    })
  } finally {
    parentSearchLoading.value = false
  }
}

const save = async () => {
  const payload = patch.value

  if (!hasAlbumWritePatch(payload)) {
    emit('saved')
    return
  }

  const album = await admin.updateAlbum(props.album.id, payload)

  if (album) {
    emit('saved')
  }
}

onMounted(() => {
  if (!tagStore.groups.length || !tagStore.tags.length) {
    void tagStore.loadAll()
  }
  void admin.loadAlbumTypes()
})
</script>

<style lang="scss" scoped>
.album-dialog {
  width: 820px;
  max-width: 94vw;

  &__cover {
    overflow: hidden;
    border-radius: 8px;
    background: #f0f2f5;
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 1;
  }

  &__disc {
    padding: 8px 10px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 8px;
    background: #fafafa;
  }

  &__disc-badge {
    min-width: 52px;
    justify-content: center;
  }

  &__tags {
    position: relative;

    &.is-locked :deep(.q-field--disabled) {
      opacity: 1;

      .q-field__control {
        background: #f6f7f9;
      }

      .q-field__native,
      .q-field__label,
      .q-chip {
        color: inherit;
        opacity: 1;
      }
    }

    :deep(.q-gutter-md) {
      margin-top: 0;
    }
  }

  &__tags-banner {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    color: #5f6368;
    font-size: 12px;
    line-height: 16px;
  }

  &__tags-shield {
    position: absolute;
    z-index: 2;
    inset: 0;
    margin: 0;
    padding: 0;
    border: 1px dashed rgba(0, 0, 0, 0.22);
    border-radius: 8px;
    background: transparent;
    cursor: pointer;

    &:hover,
    &:focus-visible {
      border-color: #f2c037;
      background: rgba(255, 243, 205, 0.28);
      outline: none;
    }
  }

  &__tags-note {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 8px;
    background: #fff6e0;
    color: #7a4e00;
    font-size: 13px;
    line-height: 1.35;
  }
}

.album-tags-confirm {
  width: 480px;
  max-width: 92vw;

  &__text {
    padding-top: 6px;
    line-height: 1.45;
  }
}
</style>
