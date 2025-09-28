<template>
  <div class="q-mb-sm">
    <q-btn
      icon="arrow_back"
      color="primary"
      :to="'/music'"
    >
      <div class="q-ml-xs">Back to the Music</div>
    </q-btn>
  </div>
  <PlaylistPageSkeleton v-if="loading"/>
  <template v-else>
    <div class="playlist">
      <div class="playlist-head q-mb-lg">
        <div class="playlist-head__left">
          <div class="playlist-head__image q-mb-md">
            <img :src="playlist?.image" alt=""/>
          </div>
        </div>
        <div class="playlist-head__right">
          <h2 class="playlist-head__name">{{ playlist?.name }}</h2>
          <div class="playlist-head__description">
            <div class="playlist-head__description-item">{{ playlist?.description }}</div>
          </div>
        </div>
      </div>
      <div class="playlist-body">
        <MusicPlaylistTracksList
          v-if="playlist?.relationships.tracks?.data?.length"
          :tracks="playlist?.relationships.tracks?.data"
          :playlistId="id"
        />
        <AppNoResultsPlug
          v-else
          title="Playlist is empty"
          body="Add some tracks!"
        />
      </div>
    </div>
  </template>
</template>
<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'src/boot/axios'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import MusicPlaylistTracksList from 'src/components/client/Music/MusicPlaylistTracksList.vue'
import PlaylistPageSkeleton from 'src/pages/client/Music/PlaylistPageSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { ITrack } from 'src/components/client/Music/types'

interface Playlist {
  name: string
  image: string
  description: string | null
  date: string
  relationships: {
    tracks: {
      data: ITrack[]
    }
  }
}

interface GetPlaylistApiResponse {
  data: {
    type: string
    id: string
    attributes: {
      name: string
      image: string
      description: string | null
      date: string
    }
    relationships: {
      tags: {
        data: [],
        meta: {
          tracks_count: number
        }
      }
    }
  },
  included: any
}

const route = useRoute()
const musicPlayer = useMusicPlayer()
const props = defineProps<{
  id: string
}>()

const loading = ref(true)
const playlist = ref<Playlist>()

const getPlaylist = async (id: string | string[]): Promise<void> => {
  const playlistId = Array.isArray(id) ? id[0] : id

  await api.get<GetPlaylistApiResponse>(`v1/music/playlists/${playlistId}`)
    .then(response => {
      const responsePlaylist = response.data.data
      playlist.value = {
        ...responsePlaylist.attributes,
        relationships: {
          tracks: getIncluded<ITrack>('tracks', responsePlaylist.relationships, response.data.included) as { data: ITrack[] }
        }
      }
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

onMounted(() => {
  getPlaylist(props.id)
})

watch(() => route.params, (toParams) => {
  getPlaylist(toParams.id)
})

musicPlayer.init()
</script>

<style lang="scss" scoped>
.playlist-head {
  display: flex;
  column-gap: 1rem;
  padding: 1rem 0 0 0;

  &__image {
    & > * {
      width: 200px;
      height: 200px;
    }
  }

  &__name {
    margin: 0 0 1rem 0;
    font-size: 45px;
    line-height: 45px;
    font-weight: 700;
  }

  &__description {
    margin: 0 0 1rem 0;

    &-item {
      margin-bottom: 1rem;
    }
  }
}

.playlist-body {
  background: #fff;
}
</style>
