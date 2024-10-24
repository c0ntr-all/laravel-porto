<template>
  <div class="q-mb-sm">
    <AppBackButton link="/gallery" text="Back To Albums"/>
  </div>

  <div v-if="album">
    <q-card class="content-container q-mb-md" flat>
      <q-card-section class="content-container__section">
        <p>Album <b>{{ album.name }}</b></p>
        <p>Total Items: {{ total }}</p>
      </q-card-section>
      <q-card-section>
        <div class="row q-gutter-md">
          <GalleryMediaCard
            v-for="media in album.relationships.media.data"
            :key="media.id"
            :media="media"
          />
        </div>
      </q-card-section>
    </q-card>
  </div>
  <div v-else>
  </div>
</template>

<script lang="ts" setup>
import { api } from 'src/boot/axios'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import { onMounted, ref } from 'vue'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import GalleryMediaCard from 'src/components/client/Gallery/GalleryMediaCard.vue'
import { MediaItem } from 'src/components/client/Gallery/types'

interface RelationshipData {
  type: string
  id: string
}

interface Album {
  id: string
  name: string
  image: string
  description: string | null
  created_at: string
  relationships: {
    media: {
      data: MediaItem[]
    }
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
      created_at: string
    }
    relationships: {
      media: {
        data: RelationshipData[]
      }
    }
  },
  included: IncludeItem[]
}

const props = defineProps<{
  id: string
}>()
const album = ref<Album>()
const total = ref(0)
const loading = ref<boolean>(true)

const getAlbum = async (id: string): Promise<void> => {
  await api.get<GetAlbumApiResponse>(`v1/gallery/albums/${id}`)
    .then(response => {
      const responseAlbum = response.data.data
      album.value = {
        id: responseAlbum.id,
        ...responseAlbum.attributes,
        relationships: {
          media: getIncluded<MediaItem>('media', responseAlbum.relationships, response.data.included) as { data: MediaItem[] }
        }
      }
      console.log(album.value)
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

onMounted(() => {
  getAlbum(props.id)
})
</script>

<style lang="scss" scoped>
</style>
