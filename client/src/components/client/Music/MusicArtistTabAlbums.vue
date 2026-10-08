<template>
  <ArtistTabAlbumsSkeleton v-if="loading"/>
  <q-card class="q-mb-md" flat v-else>
    <q-card-section>
      <div class="artist-albums q-mb-lg">
        <div class="text-h5 q-mb-sm">Альбомы</div>

        <div
          v-if="typeOptions.length > 1"
          class="artist-albums__filters q-mb-md"
        >
          <q-btn-toggle
            v-model="selectedTypeId"
            unelevated
            dense
            no-caps
            toggle-color="primary"
            color="grey-2"
            text-color="primary"
            :options="typeOptions"
          />
        </div>

        <div
          v-if="filteredAlbums.length"
          class="row items-start q-gutter-md"
        >
          <AlbumCard
            v-for="album in filteredAlbums"
            :key="album.id"
            :album="album"
          />
        </div>
        <div
          v-else
          class="text-grey-6"
        >
          Нет альбомов выбранного типа
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { artistApi } from 'src/api/requests/artistApi'
import { mapAlbumsResponse } from 'src/api/mappers/Music/album.mapper'
import { handleApiError } from 'src/utils/jsonapi'
import AlbumCard from 'src/components/client/Music/MusicAlbumCard.vue'
import ArtistTabAlbumsSkeleton from 'src/components/client/Music/MusicArtistTabAlbumsSkeleton.vue'
import { IAlbum, IAlbumType } from 'src/types'

const ALL_TYPES = 'all'

const props = defineProps<{
  artistId: string
}>()

const loading = ref(true)
const albums = ref<IAlbum[]>([])
const selectedTypeId = ref<string>(ALL_TYPES)

const typeOptions = computed(() => {
  const byId = new Map<string, IAlbumType>()

  albums.value.forEach(album => {
    const type = album.album_type
    if (!type?.id || byId.has(type.id)) {
      return
    }
    byId.set(type.id, type)
  })

  const options = Array.from(byId.values())
    .sort((left, right) => left.label.localeCompare(right.label, 'ru'))
    .map(type => ({
      label: type.label,
      value: type.id
    }))

  if (!options.length) {
    return []
  }

  return [
    { label: 'Все', value: ALL_TYPES },
    ...options
  ]
})

const filteredAlbums = computed(() => {
  if (selectedTypeId.value === ALL_TYPES) {
    return albums.value
  }

  return albums.value.filter(album => album.album_type?.id === selectedTypeId.value)
})

const getAlbums = async (id: string): Promise<void> => {
  loading.value = true

  try {
    const response = await artistApi.getArtistAlbums(id, { sort: '-date' })
    albums.value = mapAlbumsResponse(response)
    selectedTypeId.value = ALL_TYPES
  } catch (error) {
    handleApiError(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  void getAlbums(props.artistId)
})
</script>

<style lang="scss" scoped>
.artist-albums {
  &__filters {
    overflow-x: auto;
  }
}
</style>
