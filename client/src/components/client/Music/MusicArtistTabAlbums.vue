<template>
  <ArtistTabAlbumsSkeleton v-if="loading"/>
  <q-card class="q-mb-md" flat v-else>
    <q-card-section>
      <div class="artist-albums q-mb-lg" v-if="albums">
        <div class="text-h5 q-mb-sm">Альбомы</div>
        <div class="row items-start q-gutter-md">
          <AlbumCard
            v-for="album in albums"
            :key="album.id"
            :album="album"
          />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import { handleApiError } from 'src/utils/jsonapi'
import AlbumCard from 'src/components/client/Music/MusicAlbumCard.vue'
import ArtistTabAlbumsSkeleton from 'src/components/client/Music/MusicArtistTabAlbumsSkeleton.vue'
import { IAlbum } from 'src/components/client/Music/types'

interface IRelation {
  data: {
    type: string
    id: string
  }
}

interface IResponseAlbum {
  type: string
  id: string
  attributes: {
    name: string
    image: string
    date: string
  }
  relationships: {
    artists: IRelation[]
    tags: IRelation[]
  }
}

interface IGetArtistAlbumsResponse {
  data: IResponseAlbum[]
  meta: {
    albums_count: number
  }
}

const props = defineProps<{
  artistId: string,
}>()
const loading = ref(true)
const albums = ref<IAlbum[]>()
const albumsCount = ref(0)

const getAlbums = async (id: string): Promise<void> => {
  await api.get<IGetArtistAlbumsResponse>(`v1/music/artists/${id}/albums`)
    .then(response => {
      albums.value = response.data.data.map((responseAlbum: IResponseAlbum) => {
        return {
          id: responseAlbum.id,
          name: responseAlbum.attributes.name,
          image: responseAlbum.attributes.image,
          date: responseAlbum.attributes.date
        } as IAlbum
      })
      albumsCount.value = response.data.meta.albums_count
    }).catch((error: AxiosError) => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}
onMounted(() => {
  getAlbums(props.artistId)
})
</script>

<style scoped>

</style>
