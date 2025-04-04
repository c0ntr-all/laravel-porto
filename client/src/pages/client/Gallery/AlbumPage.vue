<template>
  <div class="q-mb-sm">
    <AppBackButton link="/gallery" text="Back To Albums"/>
  </div>

  <template v-if="album">
    <q-card class="content-container q-mb-md" flat>
      <q-card-section class="content-container__section">
        <div class="row">
          <div class="col-md-6">
            <p>Album <b>{{ album.name }}</b></p>
            <p>{{ album.description }}</p>
          </div>
          <div class="col-md-6 flex justify-end items-start">
            <GalleryUploadButton/>
          </div>
        </div>
      </q-card-section>
      <q-card-section>
        <p>Total Items: {{ total }}</p>
        <div class="row q-gutter-md">
          <GalleryMediaCard
            v-for="media in album.relationships.media.data"
            :key="media.id"
            :media="media"
            @click="openCarousel(media.id)"
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
import { getIncluded, handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { onMounted, provide, ref } from 'vue'
import { IAlbum, IMediaItem } from 'src/components/client/Gallery/types'
import { IIncludeItem, IRelationshipData } from 'src/components/types'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import GalleryMediaCard from 'src/components/client/Gallery/GalleryMediaCard.vue'
import GalleryCarouselDialog from 'src/components/client/Gallery/GalleryCarouselDialog.vue'
import AlbumPageSkeleton from 'src/pages/client/Gallery/AlbumPageSkeleton.vue'
import GalleryUploadButton from 'src/components/client/Gallery/GalleryUploadButton.vue'

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

interface IResponseMediaItem {
  id: string
  type: string
  attributes: {
    type: 'photo' | 'video'
    name: string
    description: string
    path: string
  }
}

interface IUploadApiResponse {
  data: IResponseMediaItem[]
  meta: {
    message: string
  }
}

interface IWebUploadApiResponse {
  data: IResponseMediaItem
  meta: {
    message: string
  }
}

const props = defineProps<{
  id: string
}>()
const album = ref<IAlbum | null>(null)
const total = ref(0)
const loading = ref<boolean>(true)
const showCarousel = ref(false)
const currentSlide = ref<string>('')

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

const uploadMedia = async (data: [] | string, type: string): Promise<void> => {
  const endpoints: Record<typeof type, string> = {
    windows: `v1/gallery/albums/${album.value!.id}/media/upload-windows`,
    web: `v1/gallery/albums/${album.value!.id}/media/upload-web`
  }

  await api.post<IUploadApiResponse | IWebUploadApiResponse>(
    endpoints[type],
    type === 'windows' ? { paths: data } : { link: data }
  ).then(response => {
    const responseData = response.data.data

    const newMedia = Array.isArray(responseData)
      ? responseData.map(formatMediaItem)
      : [formatMediaItem(responseData)]

    addMediaToAlbum(newMedia)

    handleApiSuccess(response)
  }).catch(error => {
    handleApiError(error)
  })
}

const formatMediaItem = (item: IResponseMediaItem): IMediaItem => ({
  id: item.id,
  ...item.attributes
})

const addMediaToAlbum = (media: IMediaItem[]) => {
  album.value?.relationships.media.data.push(...media)
}

const openCarousel = (id: string) => {
  currentSlide.value = id
  showCarousel.value = true
}

provide('albumId', props.id)
provide('uploadMedia', uploadMedia)
provide('addMediaToAlbum', addMediaToAlbum)

onMounted(() => {
  getAlbum(props.id)
})
</script>

<style lang="scss" scoped>
</style>
