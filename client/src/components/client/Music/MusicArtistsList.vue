<template>
  <template v-if="props.artists?.length">
    <div v-if="cardMode === 'card'" class="artists-list row items-start q-gutter-md q-mb-lg">
      <template v-if="loading">
        <div class="column q-gutter-md q-mb-lg">
          <MusicArtistCardRowSkeleton v-for="n in 20" :key="n"/>
        </div>
      </template>
      <template v-else>
        <MusicArtistCard
          v-for="artist in props.artists"
          :key="artist.id"
          :artist="artist"
        />
      </template>
    </div>
    <div v-if="cardMode === 'row'" class="column q-gutter-md q-mb-lg">
      <template v-if="loading">
        <div class="column q-gutter-md q-mb-lg">
          <MusicArtistCardRowSkeleton v-for="n in 20" :key="n"/>
        </div>
      </template>
      <template v-else>
        <MusicArtistCardRow
          v-for="artist in props.artists"
          :key="artist.id"
          :artist="artist"
        />
      </template>
    </div>
    <div class="show-more-button flex justify-center">
      <!--                <q-btn-->
      <!--                  v-if="pagination.hasPages"-->
      <!--                  color="primary"-->
      <!--                  label="Show more"-->
      <!--                  @click="loadMoreArtists"-->
      <!--                  :loading="paginationLoading"-->
      <!--                />-->
    </div>
  </template>
  <AppNoResultsPlug v-else/>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import MusicArtistCard from 'src/components/client/Music/MusicArtistCard.vue'
import MusicArtistCardRow from 'src/components/client/Music/MusicArtistCardRow.vue'
import MusicArtistCardRowSkeleton from 'src/components/client/Music/MusicArtistCardRowSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { IArtist } from 'src/components/client/Music/types'

interface Pagination {
  perPage: number
  hasPages: boolean
  nextPageUrl: string
  prevPageUrl: string
}

const props = defineProps<{
  artists: IArtist[],
  pagination: Pagination,
  cardMode: string
}>()
const loading = ref<boolean>(false)
const cardMode = ref(props.cardMode)
</script>
