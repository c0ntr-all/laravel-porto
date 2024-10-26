<template>
  <div class="q-mb-sm">
    <AppBackButton link="/gallery" text="Back To Albums"/>
  </div>

  <template v-if="album">
    <q-card class="content-container q-mb-md" flat>
      <q-card-section class="content-container__section">
        <p>Album <b>{{ album.name }}</b></p>
        <p>{{ album.description }}</p>
      </q-card-section>
      <q-card-section>
        <p>Total Items: {{ total }}</p>
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

    <GalleryCarouselDialog
      v-model="showCarousel"
      :currentSlide="currentSlide"
      :slides="album.relationships.media.data"
    />
  </template>

  <template v-else>
    <AlbumPageSkeleton/>
  </template>
</template>

<script lang="ts" setup>
import { api } from 'src/boot/axios'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import { onMounted, ref } from 'vue'
import { IAlbum, IMediaItem } from 'src/components/client/Gallery/types'
import { IIncludeItem, IRelationshipData } from 'src/components/types'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import GalleryMediaCard from 'src/components/client/Gallery/GalleryMediaCard.vue'
import GalleryCarouselDialog from 'src/components/client/Gallery/GalleryCarouselDialog.vue'
import AlbumPageSkeleton from 'src/pages/client/Gallery/AlbumPageSkeleton.vue'

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
        meta: {
          count: number
        }
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
const showCarousel = ref(false)
const currentSlide = ref()

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
      total.value = responseAlbum.relationships.media.meta.count
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

const handleCarousel = (media: IMediaItem) => {
  currentSlide.value = media.id
  showCarousel.value = true
}

onMounted(() => {
  getAlbum(props.id)
})
</script>

<style lang="scss" scoped>
</style>
