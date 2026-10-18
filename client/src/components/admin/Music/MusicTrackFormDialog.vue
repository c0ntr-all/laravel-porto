<template>
  <q-dialog v-model="show">
    <q-card class="track-dialog">
      <q-card-section class="row items-center">
        <div>
          <div class="text-h6">Edit track</div>
          <div class="text-caption text-grey-7">
            <template v-if="track.album">{{ track.album.name }}</template>
            <template v-else>Standalone track</template>
          </div>
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row q-col-gutter-lg">
          <div class="col-12 col-sm-4">
            <div class="track-dialog__cover q-mb-sm">
              <q-img v-if="coverPreview" :src="coverPreview" ratio="1" />
              <div v-else class="track-dialog__placeholder">
                <q-icon name="music_note" size="64px" color="grey-5" />
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
            <q-input
              v-model="name"
              label="Name"
              outlined
              dense
              :rules="[val => Boolean(val && String(val).trim()) || 'Name is required']"
            />
            <div class="row q-col-gutter-sm">
              <div class="col-4">
                <q-input
                  v-model="number"
                  label="Track #"
                  type="number"
                  min="1"
                  outlined
                  dense
                  clearable
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model="cd"
                  label="CD"
                  outlined
                  dense
                  clearable
                  hint="Disc number, e.g. 1"
                />
              </div>
              <div class="col-4">
                <q-input
                  :model-value="track.duration"
                  label="Duration"
                  outlined
                  dense
                  readonly
                />
              </div>
            </div>
            <q-input
              v-model="credits"
              label="Credits"
              type="textarea"
              autogrow
              outlined
              dense
              hint="Featured artists / production credits"
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
            <div class="text-subtitle2">Tags</div>
            <MusicTagGroupSelect v-model="tagIds" />
          </div>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-actions align="right">
        <q-btn flat no-caps v-close-popup>Cancel</q-btn>
        <q-btn
          color="primary"
          unelevated
          no-caps
          :loading="admin.isTrackSaving"
          :disable="!name.trim() || !isDirty"
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
import { hasTrackWritePatch, mapTrackWritePatch } from 'src/api/mappers/Music/track.mapper'
import { IArtistShort, ITrack, ITrackFormState } from 'src/types'

const props = defineProps<{
  modelValue: boolean
  track: ITrack
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: [track: ITrack]
}>()

const admin = useMusicAdminStore()
const tagStore = useMusicTagStore()
const show = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})

const name = ref(props.track.name)
const number = ref(props.track.number != null ? String(props.track.number) : '')
const cd = ref(props.track.cd ?? '')
const credits = ref(props.track.credits ?? '')
const artistIds = ref((props.track.artists ?? []).map(artist => artist.id))
const tagIds = ref((props.track.tags ?? []).map(tag => tag.id))
const imageFile = ref<File | null>(null)
const artistOptions = ref<IArtistShort[]>([...(props.track.artists ?? [])])

const coverPreview = computed(() => {
  if (imageFile.value) {
    return URL.createObjectURL(imageFile.value)
  }

  return props.track.image || props.track.album?.image || ''
})

const hydrate = (track: ITrack) => {
  name.value = track.name
  number.value = track.number != null ? String(track.number) : ''
  cd.value = track.cd ?? ''
  credits.value = track.credits ?? ''
  artistIds.value = (track.artists ?? []).map(artist => artist.id)
  tagIds.value = (track.tags ?? []).map(tag => tag.id)
  imageFile.value = null
  artistOptions.value = [...(track.artists ?? [])]
}

watch(() => props.track, hydrate)

const formState = computed((): ITrackFormState => ({
  name: name.value,
  number: number.value,
  cd: cd.value,
  credits: credits.value,
  artist_ids: artistIds.value,
  tag_ids: tagIds.value,
  image_file: imageFile.value
}))

const patch = computed(() => mapTrackWritePatch(props.track, formState.value))
const isDirty = computed(() => hasTrackWritePatch(patch.value))

const filterArtists = async (val: string, update: (fn: () => void) => void) => {
  const found = await admin.searchArtistOptions(val)
  update(() => {
    const merged = new Map<string, IArtistShort>()
    ;[...(props.track.artists ?? []), ...found].forEach(artist => {
      merged.set(artist.id, { id: artist.id, name: artist.name })
    })
    artistOptions.value = [...merged.values()]
  })
}

const save = async () => {
  const payload = patch.value

  if (!hasTrackWritePatch(payload)) {
    emit('saved', props.track)
    return
  }

  const track = await admin.updateTrack(props.track.id, payload)

  if (track) {
    emit('saved', track)
  }
}

onMounted(() => {
  if (!tagStore.groups.length || !tagStore.tags.length) {
    void tagStore.loadAll()
  }
})
</script>

<style lang="scss" scoped>
.track-dialog {
  width: 760px;
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
