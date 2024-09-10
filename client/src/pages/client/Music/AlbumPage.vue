<template>
  <AlbumPageSkeleton v-if="loading"/>
  <template v-else>
    <div class="album">
      <div class="album-head q-mb-lg">
        <div class="album-head__left">
          <div class="album-head__image q-mb-md">
            <a :href="album?.image" @click.prevent="showImage = true">
              <img :src="album?.image" alt="">
            </a>
            <q-dialog v-model="showImage">
              <img :src="album?.image" alt="">
            </q-dialog>
          </div>
          <div class="album-head__actions">
            <q-btn @click="addToPlaylist" icon="playlist_add" dense>
              <q-tooltip anchor="top middle" self="bottom middle" :offset="[5, 5]">
                <span style="font-size: .75rem">Add to playlist</span>
              </q-tooltip>
            </q-btn>
          </div>
        </div>
        <div class="album-head__right">
          <h2 class="album-head__name">{{ album?.name }}</h2>
          <div class="album-head__description">
            <p class="album-head__description-item">
              <template
                v-for="(artist, index) in album?.relationships.artists.data"
                :key="artist.id"
              >
                <router-link :to="`/music/artists/${artist.id}`">{{ artist.name }}</router-link>
                <span v-if="album && index < album?.relationships?.artists?.data?.length - 1"> • </span>
              </template>
            </p>
            <p class="album-head__description-item">{{ album?.date }}</p>
            <div class="album-head__description-item">
              {{ album?.description }}
            </div>
          </div>
          <div class="album-head__tags">
            <div class="tags-list q-gutter-sm">
              <q-chip
                v-for="tag in album?.relationships.tags.data"
                :key="tag.id"
                color="primary"
                text-color="white"
              >{{ tag.name }}
              </q-chip>
            </div>
          </div>
        </div>
      </div>
      <div class="album-body q-mb-lg bg-white">
        <MusicAlbumTracksList
          :tracks="album?.relationships.tracks"
        />
      </div>
    </div>
    <MusicAlbumVersionsList
      v-if="album"
      :versions="album.relationships.versions"
    />
  </template>
</template>

<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'src/boot/axios'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import AlbumPageSkeleton from 'src/pages/client/Music/AlbumPageSkeleton.vue'
import MusicAlbumVersionsList from 'src/components/client/Music/MusicAlbumVersionsList.vue'
import MusicAlbumTracksList from 'src/components/client/Music/MusicAlbumTracksList.vue'

interface Artist {
  id: string
  name: string
}

interface Tag {
  id: string
  name: string
}

interface AlbumVersion {
  id: string
  name: string
  content: string | null
  date: string
  image: string
}

interface Track {
  id: string
  name: string
  number: number
  artist: string
  image: string
  duration: string
  rate: number
}

interface Album {
  id: string
  name: string
  image: string
  description: string | null
  date: string
  relationships: {
    artists: {
      data: Artist[]
    }
    tags: {
      data: Tag[]
    }
    tracks: {
      data: Track[]
    }
    versions: {
      data: AlbumVersion[],
      meta: {
        count: number
      }
    }
  }
}

interface RelationshipData {
  type: string
  id: string
}

interface Relationships {
  [key: string]: {
    data: RelationshipData[]
  }
}

interface IncludeItem {
  type: string
  id: string
  attributes: object
}

interface GetAlbumApiResponse {
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
      artists: {
        data: Relationships[]
      }
      tags: {
        data: Relationships[]
      }
      tracks: {
        data: Relationships[]
      }
      versions: {
        data: Relationships[]
        meta: {
          count: number
        }
      }
    }
  },
  included: IncludeItem[]
}

const route = useRoute()
const musicPlayer = useMusicPlayer()

const props = defineProps<{
  id: string
}>()
const loading = ref(true)
const showImage = ref(false)
const album = ref<Album | null>(null)

const getAlbum = async (id: string): Promise<void> => {
  await api.get<GetAlbumApiResponse>(`v1/music/albums/${id}`)
    .then(response => {
      const responseAlbum = response.data.data
      album.value = {
        id: responseAlbum.id,
        ...responseAlbum.attributes,
        relationships: {
          artists: getIncluded<Artist>('artists', responseAlbum.relationships, response.data.included),
          tags: getIncluded<Tag>('tags', responseAlbum.relationships, response.data.included),
          tracks: getIncluded<Track>('tracks', responseAlbum.relationships, response.data.included),
          versions: {
            ...getIncluded<AlbumVersion>('versions', responseAlbum.relationships, response.data.included),
            meta: {
              count: responseAlbum.relationships.versions.meta.count
            }
          }
        }
      }
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

const addToPlaylist = () => {
  if (album.value?.relationships.tracks) {
    musicPlayer.addToPlaylist(album.value.relationships.tracks.data)
  }
}

onMounted(() => {
  getAlbum(props.id)
})

watch(
  () => route.params,
  (toParams) => {
    getAlbum(toParams.id as string)
  }
)
</script>

<style lang="scss" scoped>
.album-head {
  display: flex;
  column-gap: 1rem;
  padding: 1rem 0 0 0;

  &__image {
    img {
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

.album-tracks {
  &__header {
    display: flex;
    align-items: center;
    position: relative;
    height: auto;
    min-height: 45px;
    border-bottom: 1px solid #d7d7d7;

    &-number {
      flex: 0 0 40px;
      text-align: center;
    }

    &-name {
      flex: 1 1 100%;
    }

    &-favorite {
      display: flex;
      flex: 1 0 45px;
      justify-content: center;
      margin-right: 15px;
    }
  }
}

.album-versions {
  max-width: 720px;
  border-right: 1px solid #ccc;

  &__row {
    &:hover {
      cursor: pointer;
    }
  }
}

.album-year {
  color: #777;
}
</style>
