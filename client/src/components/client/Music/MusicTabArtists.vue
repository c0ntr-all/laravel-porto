<template>
  <div class="row q-col-gutter-md q-mb-md">
    <div class="col-lg-3 col-md-4">
      <q-card flat>
        <q-card-section>
          <div class="flex justify-between items-end">
            <MusicTabArtistsFilter
              @submitFilter="reloadArtists"
              @resetFilter="reloadArtists"
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
          <MusicArtistsListSkeleton v-if="catalog.isArtistsLoading"/>
          <template v-else-if="catalog.artists.length">
            <MusicArtistsList
              :artists="catalog.artists"
              :card-mode="cardMode"
            />
            <div
              v-if="catalog.hasMoreArtists"
              ref="sentinel"
              class="artists-list-sentinel"
            />
            <div
              v-if="catalog.isArtistsLoadingMore"
              class="flex justify-center q-my-md"
            >
              <q-spinner color="primary" size="2em"/>
            </div>
          </template>
          <AppNoResultsPlug v-else/>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import MusicArtistsList from 'src/components/client/Music/MusicArtistsList.vue'
import MusicArtistsListSkeleton from 'src/components/client/Music/MusicArtistsListSkeleton.vue'
import MusicTabArtistsFilter from 'src/components/client/Music/MusicTabArtistsFilter.vue'
import MusicTabArtistsSearch from 'src/components/client/Music/MusicTabArtistsSearch.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const catalog = useMusicCatalogStore()
const cardMode = ref<'card' | 'row'>('row')
const sentinel = ref<HTMLElement | null>(null)

let observer: IntersectionObserver | null = null

const reloadArtists = () => {
  return catalog.getArtists()
}

const search = (searchText: string) => {
  return catalog.getArtists({ name: searchText })
}

const resetSearch = () => {
  return catalog.getArtists({ name: '' })
}

const switchCardMode = (mode: 'card' | 'row') => {
  cardMode.value = mode
}

const disconnectObserver = () => {
  observer?.disconnect()
  observer = null
}

const observeSentinel = () => {
  disconnectObserver()

  if (!sentinel.value) {
    return
  }

  observer = new IntersectionObserver((entries) => {
    if (!entries.some(entry => entry.isIntersecting)) {
      return
    }

    if (!catalog.hasMoreArtists || catalog.isArtistsLoading || catalog.isArtistsLoadingMore) {
      return
    }

    void catalog.getArtists({ append: true })
  }, {
    root: null,
    rootMargin: '320px 0px',
    threshold: 0
  })

  observer.observe(sentinel.value)
}

watch(sentinel, () => {
  observeSentinel()
}, { flush: 'post' })

onMounted(() => {
  void catalog.getArtists()
})

onUnmounted(() => {
  disconnectObserver()
})
</script>

<style lang="scss" scoped>
.artists-list-sentinel {
  width: 100%;
  height: 1px;
}
</style>
