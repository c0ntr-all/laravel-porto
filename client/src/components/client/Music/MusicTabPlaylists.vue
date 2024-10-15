<template>
  <p class="text-h6 q-pa-md">Total: {{ playlistsCount }}</p>
  <q-btn
    class="q-mb-lg"
    @click="createPlaylistModal = true"
    icon="add"
    label="Create playlist"
    color="primary"
    size="md"
    dense
  />
  <q-dialog v-model="createPlaylistModal">
    <q-card class="playlist-modal">
      <q-card-section class="flex justify-between">
        <div class="text-h6">Create playlist</div>
        <q-btn @click="createPlaylistModal = false" icon="close" size="md" flat rounded dense/>
      </q-card-section>

      <q-separator/>

      <q-card-section>
        <q-input
          v-model="newPlaylistName"
          type="text"
          label="Playlist name"
          filled
          dense
        />
      </q-card-section>

      <q-separator/>

      <q-card-section class="flex justify-end">
        <q-btn class="q-px-sm q-mr-md" @click="createPlaylistModal = false" dense flat>Cancel</q-btn>
        <q-btn @click="createPlaylist" class="q-px-md" color="primary" dense>Create</q-btn>
      </q-card-section>
    </q-card>
  </q-dialog>

  <q-card class="q-mb-md" flat bordered>
    <q-card-section>
      <div class="row q-gutter-md">
        <template v-if="loading">
          <MusicPlaylistCardSkeleton
            v-for="q in Math.floor(Math.random() * 10 + 1)"
            :key="q"
          />
        </template>
        <template v-else>
          <template v-if="playlists.length">
            <MusicPlaylistCard
              v-for="playlist in playlists"
              :key="playlist.id"
              :playlist="playlist"
            />
          </template>
          <AppNoResultsPlug
            v-else
            title="There are no playlists yet."
            body="Try to create!"
          />
        </template>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError } from 'src/utils/jsonapi'
import MusicPlaylistCard from 'src/components/client/Music/MusicPlaylistCard.vue'
import MusicPlaylistCardSkeleton from 'src/components/client/Music/MusicPlaylistCardSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { IPlaylist } from 'src/components/client/Music/types'

interface IResponsePlaylist {
  type: string
  id: string
  attributes: {
    name: string
    image: string
    created_at: string
  }
}

interface IGetPlaylistsResponse {
  data: IResponsePlaylist[]
  meta: {
    playlists_count: number
  }
}

interface ICreatePlaylistResponse {
  data: {
    type: string
    id: string
    attributes: {
      name: string
      image: string
      created_at: string
    }
  }
  meta: {
    playlists_count: number
  }
}

const loading = ref(true)
const createPlaylistModal = ref(false)
const newPlaylistName = ref('')
const playlists = ref<IPlaylist[]>([])
const playlistsCount = ref(0)

const getPlaylists = async (): Promise<void> => {
  await api.get<IGetPlaylistsResponse>('v1/music/playlists')
    .then(response => {
      playlists.value = response.data.data.map((responsePlaylist: IResponsePlaylist) => {
        return {
          id: responsePlaylist.id,
          ...responsePlaylist.attributes
        }
      })
      playlistsCount.value = response.data.meta.playlists_count
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

const createPlaylist = async (): Promise<void> => {
  await api.post<ICreatePlaylistResponse>('v1/music/playlists', {
    name: newPlaylistName.value
  }).then(response => {
    playlists.value.push({
      id: response.data.data.id,
      ...response.data.data.attributes
    })
    playlistsCount.value = response.data.meta.playlists_count
  }).catch(error => {
    handleApiError(error)
  }).finally(() => {
    loading.value = false
  })
}

onMounted(() => {
  getPlaylists()
})
</script>

<style lang="scss" scoped>
</style>
