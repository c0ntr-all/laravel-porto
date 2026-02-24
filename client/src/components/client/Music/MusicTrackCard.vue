<template>
  <div
    class="music-track flex no-wrap self-start items-center col-grow q-pr-sm rounded-borders"
    :class="{'music-track--active': track.id === musicPlayer.track.id}"
  >
    <div class="music-track__left" @click="handlePlay">
      <div class="music-track-cover q-mr-md">
        <q-img
          v-if="track.image"
          :src="track.image"
          class="music-track-cover__image"
          :alt="track.name"
        />
        <div class="music-track-cover__overlay">
          <div class="music-track-cover__play-icon">
            <q-spinner-audio
              v-if="musicPlayer.status === 'playing'"
              size="1rem"
              color="white"
            />
            <div class="text-white" v-else>....</div>
          </div>
        </div>
        <q-icon
          class="music-track-cover__status-icon"
          size="xs"
          :name="musicPlayer.status === 'paused' || (musicPlayer.status === 'playing' && musicPlayer.track.id !== track.id) ? 'play_arrow' : 'pause'"
          flat
          round
          dense
        />
      </div>
      <div class="music-track__title">
        <div class="music-track__name">{{ track.name }}</div>
        <div class="music-track__artist">{{ track.artist }}</div>
      </div>
    </div>
    <div class="music-track__right">
      <div class="music-track__rate q-gutter-y-md">
        <q-rating
          v-model="track.rate"
          @update:model-value="rateTrack"
          :max="4"
          size="1.5em"
          color="primary"
          :icon="[
            'sentiment_very_dissatisfied',
            'sentiment_dissatisfied',
            'sentiment_satisfied',
            'sentiment_very_satisfied'
          ]"
        />
      </div>
      <div class="music-track__end-column">
        <div class="music-track__time">
          {{ track.duration }}
        </div>
        <div class="music-track__more">
          <q-btn color="grey-7" icon="more_horiz" round flat>
            <q-menu cover auto-close>
              <q-list>
                <q-item
                  v-for="action in filteredActions"
                  :key="action.name"
                  @click="handleFunction(action.name)"
                  clickable
                >
                  <q-item-section>
                    <div class="flex items-center">
                      <q-icon
                        size="xs"
                        :name="action.icon"
                        flat
                        round
                        dense
                      />
                      <div class="q-ml-xs">{{ action.label }}</div>
                    </div>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </div>
    </div>
  </div>
  <q-dialog v-model="showPlaylistModal" @show="initPlaylistDialog">
    <q-card class="playlist-modal">
      <q-card-section class="flex justify-between">
        <div class="text-h6">Add to playlist</div>
        <q-btn @click="showPlaylistModal = false" icon="close" size="md" flat rounded dense/>
      </q-card-section>

      <q-separator/>

      <q-card-section>
        <q-input
          v-model="playlistSearch"
          type="search"
          label="Search playlist"
          filled
          dense
        >
          <template v-slot:append>
            <q-btn
              v-if="playlistSearch.length"
              @click="playlistSearch = ''"
              icon="close"
              flat
              rounded
              dense
            />
            <q-icon name="search"/>
          </template>
        </q-input>
      </q-card-section>

      <q-separator/>

      <q-card-section>
        <div class="playlists-list">
          <q-checkbox
            v-for="item in filteredPlaylists"
            :key="item.id"
            v-model="selectedPlaylistsIds"
            class="playlist-item"
            :label="item.name"
            :val="item.id"
            color="primary"
            left-label
            dense
          />
        </div>
      </q-card-section>

      <q-separator/>

      <q-card-section class="flex justify-end">
        <q-btn class="q-px-sm q-mr-md" @click="showPlaylistModal = false" dense flat>Cancel</q-btn>
        <q-btn @click="syncPlaylists" class="q-px-md" color="primary" dense>Save</q-btn>
      </q-card-section>
      <q-inner-loading :showing="playlistsLoading">
        <q-spinner-gears size="50px" color="primary"/>
      </q-inner-loading>
    </q-card>
  </q-dialog>
</template>
<script lang="ts" setup>
import { ref, computed, watch } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { getIncluded, handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { api } from 'src/boot/axios'
import { ITrack } from 'src/components/client/Music/types'

interface PlaylistTrack {
  id: string
  name: string
  image: string
}

interface Playlist {
  id: string
  name: string,
  relationships: {
    tracks: {
      data: PlaylistTrack[]
    }
  }
}

// TODO: Common Interface Exists
interface Action {
  name: string
  label: string
  icon: string
}

interface ApiResponseTrack {
  type: string
  id: string
  attributes: {
    name: string,
    image: string,
  }
}

interface ApiResponsePlaylist {
  type: string
  id: string
  attributes: {
    name: string,
    description: string | null,
    image: string,
    created_at: string
  }
  relationships: {
    tracks: {
      data: ApiResponseTrack[]
    }
  }
}

interface GetPlaylistsApiResponse {
  data: ApiResponsePlaylist[],
  included: ApiResponseTrack[],
  meta: {
    playlists_count: number
  }
}

const emit = defineEmits(['play', 'remove'])
const props = defineProps<{
  track: ITrack
  actions?: string[]
  playlistId?: string
}>()
const musicPlayer = useMusicPlayer()

const availableActions: Action[] = [{
  name: 'addToPlaylist',
  label: 'Add to playlist',
  icon: 'add'
}, {
  name: 'deleteTrackFromPlaylist',
  label: 'Remove from playlist',
  icon: 'delete'
}]

const track = ref<ITrack>({ ...props.track, rate: props.track.rate ?? 0 })
const showPlaylistModal = ref(false)
const playlists = ref<Playlist[]>([])
const playlistsLoading = ref(false)
const filteredPlaylists = ref<Playlist[]>([])
const playlistSearch = ref('')
const selectedPlaylistsIds = ref<string[]>([])

const filteredActions = computed(() => {
  return availableActions.filter(item => props.actions?.includes(item.name))
})

const rateTrack = async (value: number): Promise<void> => {
  const previousRate = track.value.rate

  await api.post(`v1/music/tracks/${track.value.id}/rate`, {
    rate: value
  }).then(response => {
    handleApiSuccess(response.data)
  }).catch(error => {
    track.value.rate = previousRate ?? 0
    handleApiError(error)
  })
}

const initPlaylistDialog = async (): Promise<void> => {
  playlistsLoading.value = true

  await api.get<GetPlaylistsApiResponse>('v1/music/playlists?include=tracks')
    .then(response => {
      const playlistsList = response.data.data.map(item => {
        const tracks = getIncluded<PlaylistTrack>('tracks', item.relationships, response.data.included) as { data: PlaylistTrack[] }
        if (tracks.data.find((track: PlaylistTrack) => track?.id === props.track.id) && !selectedPlaylistsIds.value.includes(item.id)) {
          selectedPlaylistsIds.value.push(item.id)
        }

        return {
          id: item.id,
          name: item.attributes.name,
          relationships: {
            tracks: {
              data: tracks.data
            }
          }
        }
      })

      playlists.value = playlistsList
      filteredPlaylists.value = playlistsList
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      playlistsLoading.value = false
    })
}

const handleFunction = (actionName: string) => {
  switch (actionName) {
    case 'addToPlaylist':
      showPlaylistModal.value = true
      break

    case 'deleteTrackFromPlaylist':
      deleteTrackFromPlaylist()
      break
  }
}

const syncPlaylists = async (): Promise<void> => {
  await api.put(`v1/music/tracks/${track.value.id}/playlists`, {
    playlist_ids: selectedPlaylistsIds.value
  }).then(response => {
    handleApiSuccess(response.data)
  }).catch(error => {
    handleApiError(error)
  }).finally(() => {
    showPlaylistModal.value = false
  })
}

const deleteTrackFromPlaylist = async (): Promise<void> => {
  await api.delete(`v1/music/playlists/${props.playlistId}/tracks/${track.value.id}`)
    .then(response => {
      emit('remove', track.value.id)
      handleApiSuccess(response.data)
    }).catch(error => {
      handleApiError(error)
    })
}

const handlePlay = () => {
  emit('play')
}

watch(playlistSearch, (value) => {
  filteredPlaylists.value = playlists.value.filter(item =>
    item.name.toLowerCase().includes(value.toLowerCase())
  )
})
</script>

<style lang="scss" scoped>
.music-track {
  position: relative;

  &__left {
    display: flex;
    align-items: center;
    flex-grow: 1;
    flex-shrink: 1;
    overflow: hidden;
  }

  &__right {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
  }

  &-cover {
    display: flex;
    position: relative;
    justify-content: center;
    align-items: center;
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    margin: 4px;
    border-radius: 8px;
    background: #ccc;

    &__image {
      border-radius: 8px;
    }

    &__status-icon {
      display: none;
      position: absolute;
      padding: 4px;
      background: #fff;
      border-radius: 50%;
    }

    &__play-icon {
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    &__overlay {
      display: none;
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 8px;
      background-color: rgba(0, 0, 0, .5);
    }
  }

  &__artist {
    font-size: 12.5px;
    line-height: 16px;
    font-weight: bold;
  }

  &__title {
    white-space: nowrap;
    overflow: hidden;
    margin-left: 4px;
  }

  &__name {
    font-size: 12.5px;
    line-height: 16px;
    text-overflow: ellipsis;
    overflow: hidden;
  }

  &__rate {
    margin-right: 1rem;
  }

  &__end-column {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 48px;
  }

  &__time {
    flex-shrink: 0;
    color: #818c99;
    cursor: pointer;
    font-size: 12px;
    min-width: 3em;
    text-align: right;
  }

  &__more {
    position: absolute;
    //top: 50%;
    //left: 50%;
    //margin-top: -10px;
    //margin-left: -9px;
    visibility: hidden;
  }

  &--active,
  &:hover {
    cursor: pointer;
    background-color: rgba(174, 183, 194, 0.12);

    .music-track-cover__overlay {
      display: block;
    }
  }

  &:hover {
    .music-track-cover__status-icon {
      display: flex;
    }

    .music-track__more {
      visibility: visible;
    }

    .music-track__time {
      visibility: hidden;
    }
  }
}

.playlist-modal {
  width: 768px;
  max-width: 80vw;
}

.playlists-list {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  height: 60vh;

  .playlist-item {
    display: flex;
    justify-content: space-between;
    padding: 1rem;

    &:not(:last-child) {
      border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    }

    &:hover {
      cursor: pointer;
      background: rgba(0, 0, 0, 0.05);
    }
  }
}
</style>
