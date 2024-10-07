<template>
  <div class="row q-col-gutter-md q-mb-md">
    <div class="col-lg-3 col-md-4">
      <q-card flat>
        <q-card-section>
          <div class="flex justify-between items-end">
            <MusicTabArtistsFilter
              @submitFilter="getArtists"
              @resetFilter="getArtists"
            />
          </div>
        </q-card-section>
      </q-card>
    </div>
    <div class="col-lg-9 col-md-8">
      <MusicTabArtistsSearch
        @search="search"
        @switchCardMode="switchCardMode"
        @reset="resetSearch"
      />
      <q-card flat>
        <q-card-section>
          <MusicArtistsListSkeleton v-if="loading"/>
          <template v-else>
            <MusicArtistsList
              v-if="artists.length"
              :pagination="pagination"
              :artists="artists"
              :card-mode="cardMode"
            />
            <AppNoResultsPlug v-else/>
          </template>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { AxiosError } from 'axios'

import { api } from 'src/boot/axios'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import MusicArtistsList from 'src/components/client/Music/MusicArtistsList.vue'
import MusicArtistsListSkeleton from 'src/components/client/Music/MusicArtistsListSkeleton.vue'
import MusicTabArtistsFilter from 'src/components/client/Music/MusicTabArtistsFilter.vue'
import MusicTabArtistsSearch from 'src/components/client/Music/MusicTabArtistsSearch.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

interface Tag {
  id: string
  name: string
}

interface Artist {
  id: string
  name: string
  image: string
  content: string
  relationships: {
    tags: {
      data: Tag[]
    }
  }
}

interface ResponseArtist {
  type: string
  id: string
  attributes: {
    name: string
    content: string
    image: string
    created_at: string
  }
  relationships: any
}

interface GetArtistsApiResponse {
  data: ResponseArtist[],
  included: any
  meta: {
    message?: string
  }
}

interface Pagination {
  perPage: number
  hasPages: boolean
  nextPageUrl: string
  prevPageUrl: string
}

const artists = ref<Artist[]>([])
const pagination = ref<Pagination>({
  perPage: 0,
  hasPages: false,
  nextPageUrl: '',
  prevPageUrl: ''
})
const loading = ref(true)
const cardMode = ref<'card' | 'row'>('row')

const getArtists = async(filters?: Record<string, any>): Promise<void> => {
  loading.value = true
  filters = filters || {}

  await api.get<GetArtistsApiResponse>('v1/music/artists', filters).then(response => {
    artists.value = (response.data.data as ResponseArtist[]).map((responseArtist: ResponseArtist) => {
      return {
        id: responseArtist.id,
        name: responseArtist.attributes.name,
        content: responseArtist.attributes.content,
        image: responseArtist.attributes.image,
        relationships: {
          tags: getIncluded<Tag>('tags', responseArtist.relationships, response.data.included) as { data: Tag[] }
        }
      }
    })
    // pagination.value = response.data.data.pagination
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  }).finally(() => {
    loading.value = false
  })
}

const loadMoreArtists = async (): Promise<void> => {
  if (pagination.value.hasPages) {
    loading.value = true
    const obUrl = new URL(pagination.value.nextPageUrl)
    const cursor = obUrl.searchParams.get('cursor')

    await api.post('music/artists', { cursor }).then(response => {
      pagination.value = response.data.data.pagination
      artists.value.push(...response.data.data.items)
    }).catch((error: AxiosError<{ message: string }>) => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
  }
}

const search = (searchText: string) => {
  getArtists({ filters: { search: searchText } })
}

const resetSearch = () => {
  getArtists()
}

const switchCardMode = (mode: 'card' | 'row') => {
  cardMode.value = mode
}

onMounted(() => {
  window.onscroll = () => {
    const bottomWindow = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight

    if (bottomWindow) {
      loadMoreArtists()
    }
  }

  getArtists()
})
</script>
