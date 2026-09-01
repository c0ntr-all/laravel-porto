<template>
  <q-dialog v-model="show">
    <q-card class="artist-dialog">
      <q-card-section class="row items-center">
        <div class="text-h6">Edit artist</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row q-col-gutter-lg">
          <div class="col-12 col-sm-4">
            <div class="artist-dialog__cover q-mb-sm">
              <q-img
                v-if="coverPreview"
                :src="coverPreview"
                ratio="1"
              />
              <div v-else class="artist-dialog__placeholder">
                <q-icon name="person" size="64px" color="grey-5" />
              </div>
            </div>
            <q-file
              v-model="imageFile"
              label="Cover image"
              accept="image/*"
              outlined
              dense
              clearable
            >
              <template #prepend>
                <q-icon name="image" />
              </template>
            </q-file>
          </div>
          <div class="col-12 col-sm-8">
            <q-input
              v-model="name"
              label="Name"
              outlined
              dense
              class="q-mb-md"
              :rules="[val => Boolean(val && String(val).trim()) || 'Name is required']"
            />
            <q-input
              v-model="description"
              label="Description"
              type="textarea"
              autogrow
              outlined
              dense
              class="q-mb-md"
            />
            <div class="text-subtitle2 q-mb-sm">Tags</div>
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
          :loading="admin.isArtistSaving"
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
import { IArtist } from 'src/types'

const props = defineProps<{
  modelValue: boolean
  artist: IArtist
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  saved: [artist: IArtist]
}>()

const admin = useMusicAdminStore()
const tagStore = useMusicTagStore()
const show = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})
const name = ref(props.artist.name)
const description = ref(props.artist.description ?? '')
const tagIds = ref(props.artist.tags.map(tag => tag.id))
const imageFile = ref<File | null>(null)

const coverPreview = computed(() => {
  if (imageFile.value) {
    return URL.createObjectURL(imageFile.value)
  }

  return props.artist.image || ''
})

watch(() => props.artist, artist => {
  name.value = artist.name
  description.value = artist.description ?? ''
  tagIds.value = artist.tags.map(tag => tag.id)
  imageFile.value = null
})

const save = async () => {
  const artist = await admin.updateArtist(props.artist.id, {
    name: name.value.trim(),
    description: description.value.trim() || null,
    tags: tagIds.value,
    image_file: imageFile.value
  })

  if (artist) {
    emit('saved', artist)
  }
}

onMounted(() => {
  if (!tagStore.groups.length || !tagStore.tags.length) {
    void tagStore.loadAll()
  }
})
</script>

<style lang="scss" scoped>
.artist-dialog {
  width: 760px;
  max-width: 92vw;

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
