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
            @click="handleCarousel(media)"
          />
        </div>
      </q-card-section>
    </q-card>
  </div>
  <div v-else>
  </div>

  <div v-if="album">
    <q-dialog v-model="show">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section>
          <q-carousel
            v-model="slide"
            class="text-white shadow-1 rounded-borders"
            transition-prev="scale"
            transition-next="scale"
            control-color="primary"
            swipeable
            animated
            padding
            arrows
          >
            <q-carousel-slide
              v-for="slide in album.relationships.media.data"
              :key="slide.id"
              class="column no-wrap flex-center"
              :name="slide.id"
            >
              <q-img :src="slide.path"/>
            </q-carousel-slide>
          </q-carousel>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { api } from 'src/boot/axios'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import { onMounted, ref } from 'vue'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import GalleryMediaCard from 'src/components/client/Gallery/GalleryMediaCard.vue'
import { IAlbum, IMediaItem } from 'src/components/client/Gallery/types'
import { IIncludeItem, IRelationshipData } from 'src/components/types'

interface IGetAlbumApiResponse {
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
        data: IRelationshipData[]
      }
    }
  },
  included: IIncludeItem[]
}

const props = defineProps<{
  id: string
}>()
const album = ref<IAlbum>()
const total = ref(0)
const loading = ref<boolean>(true)
const slide = ref()
const show = ref(false)

const getAlbum = async (id: string): Promise<void> => {
  await api.get<IGetAlbumApiResponse>(`v1/gallery/albums/${id}`)
    .then(response => {
      const responseAlbum = response.data.data
      album.value = {
        id: responseAlbum.id,
        ...responseAlbum.attributes,
        relationships: {
          media: getIncluded<IMediaItem>('media', responseAlbum.relationships, response.data.included) as {
            data: IMediaItem[]
          }
        }
      }
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

const handleCarousel = (media: IMediaItem) => {
  slide.value = media.id
  show.value = true
}

onMounted(() => {
  getAlbum(props.id)
})
</script>

<style lang="scss" scoped>
</style>
