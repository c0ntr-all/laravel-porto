<template>
  <template v-if="albums">
    <q-card class="content-container q-mb-md" flat>
      <q-card-section class="content-container__section">
        <div class="row q-gutter-md">
          <q-card v-for="album in albums" :key="album.id" class="album-card col-2">
            <q-img :src="album.image" :alt="album.name" :height="'100%'">
              <div class="absolute-bottom text-h6">
                <router-link :to="`/gallery/albums/${album.id}`" class="album-card__link">
                  {{ album.name }}
                </router-link>
              </div>
            </q-img>
          </q-card>
        </div>
      </q-card-section>
    </q-card>
  </template>
  <template v-else>
    <GalleryPageSkeleton/>
  </template>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError } from 'src/utils/jsonapi'
import { IAlbum } from 'src/components/client/Gallery/types'
import { IIncludedItem } from 'src/components/types'
import GalleryPageSkeleton from 'src/pages/client/Gallery/GalleryPageSkeleton.vue'

interface IResponseAlbum {
  type: string
  id: string
  attributes: {
    name: string
    image: string
    description: string | null
    created_at: string
  }
}

interface IGetAlbumsApiResponse {
  data: IResponseAlbum[],
  included: IIncludedItem[]
}

const albums = ref<IAlbum[]>()
const loading = ref(true)

const getAlbums = async (): Promise<void> => {
  await api.get<IGetAlbumsApiResponse>('v1/gallery/albums')
    .then(response => {
      albums.value = response.data.data.map((responseAlbum: IResponseAlbum) => {
        return {
          id: responseAlbum.id,
          ...responseAlbum.attributes
        }
      })
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

onMounted(() => {
  getAlbums()
})
</script>

<style lang="scss" scoped>
.album-card {
  width: 100%;
  max-width: 250px;

  &__link {
    text-decoration: none;
    color: #fff;

    &:hover {
      color: #ccc;
    }
  }
}
</style>
