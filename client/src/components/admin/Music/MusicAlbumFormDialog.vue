<template>
  <q-dialog v-model="show" persistent>
    <q-card class="album-dialog">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6">Edit album</div>
          <div class="text-caption text-grey-7">
            {{ album.is_version ? 'This release is grouped as a version' : 'Main release' }}
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
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-select
                  v-model="edition"
                  :options="editionOptions"
                  label="Edition / version"
                  use-input
                  new-value-mode="add-unique"
                  outlined
                  dense
                  clearable
                  input-debounce="0"
                  @filter="filterEdition"
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
              hint="Leave empty for a standalone original release"
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
            <MusicTagGroupSelect v-model="tagIds" />
          </div>
        </div>

        <div v-if="album.versions.length" class="q-mt-lg">
          <div class="text-subtitle2 q-mb-sm">Versions of this album</div>
          <q-list bordered separator dense>
            <q-item v-for="version in album.versions" :key="version.id">
              <q-item-section>
                {{ version.name }}
                <span v-if="version.edition" class="text-grey-7"> · {{ version.edition }}</span>
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
          :disable="!name.trim()"
          @click="save"
        >
          Save
        </q-btn>
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
import { IAlbum, IArtistShort } from 'src/types'

const EDITIONS = [
  'Original',
  'Remastered',
  'Rerecorded',
  'Deluxe',
  'Anniversary',
  'Live',
  'Instrumental',
  'Demo'
]

const props = defineProps<{
  modelValue: boolean
  album: IAlbum
  parentOptions: IAlbum[]
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
const edition = ref(props.album.edition)
const date = ref(props.album.date ?? '')
const parentId = ref(props.album.parent_id)
const artistIds = ref(props.album.artists.map(artist => artist.id))
const tagIds = ref(props.album.tags.map(tag => tag.id))
const imageFile = ref<File | null>(null)
const editionOptions = ref([...EDITIONS])
const artistOptions = ref<IArtistShort[]>([...props.album.artists])
const extraParents = ref<IAlbum[]>([])

const coverPreview = computed(() => {
  if (imageFile.value) {
    return URL.createObjectURL(imageFile.value)
  }

  return props.album.image || ''
})

const parentSelectOptions = computed(() => {
  const merged = new Map<string, IAlbum>()
  ;[...props.parentOptions, ...extraParents.value].forEach(item => {
    if (item.id !== props.album.id && !item.parent_id) {
      merged.set(item.id, item)
    }
  })

  const options = [...merged.values()].map(item => ({
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
  edition.value = album.edition
  date.value = album.date ?? ''
  parentId.value = album.parent_id
  artistIds.value = album.artists.map(artist => artist.id)
  tagIds.value = album.tags.map(tag => tag.id)
  imageFile.value = null
  artistOptions.value = [...album.artists]
}

watch(() => props.album, hydrate)

const filterEdition = (val: string, update: (fn: () => void) => void) => {
  update(() => {
    const needle = val.toLowerCase()
    editionOptions.value = EDITIONS.filter(item => item.toLowerCase().includes(needle))
  })
}

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
  const found = await admin.searchAlbumOptions(val)
  update(() => {
    extraParents.value = found
  })
}

const save = async () => {
  const album = await admin.updateAlbum(props.album.id, {
    name: name.value.trim(),
    description: description.value.trim() || null,
    edition: edition.value,
    date: date.value || null,
    parent_id: parentId.value ? Number(parentId.value) : null,
    artist_ids: artistIds.value.map(Number),
    tags: tagIds.value.map(Number),
    image_file: imageFile.value
  })

  if (album) {
    emit('saved')
  }
}

onMounted(() => {
  if (!tagStore.groups.length || !tagStore.tags.length) {
    void tagStore.loadAll()
  }
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
}
</style>
