<template>
  <div>
    <div class="artist-tracks-toolbar q-mb-md">
      <MusicTrackRateFilter
        class="artist-tracks-toolbar__rates"
        :model-value="catalog.artistTrackListRates"
        :show-label="false"
        inline
        @update:model-value="onRatesChange"
      />

      <div class="artist-tracks-toolbar__divider" aria-hidden="true" />

      <q-btn-group class="artist-tracks-toolbar__sort" flat>
        <q-btn
          unelevated
          dense
          :color="isRateAsc ? 'primary' : 'grey-2'"
          :text-color="isRateAsc ? 'white' : 'grey-8'"
          icon="north"
          aria-label="Sort by rating, lowest first"
          @click="sortByRate(false)"
        >
          <q-tooltip>Rating: low → high</q-tooltip>
        </q-btn>
        <q-btn
          unelevated
          dense
          :color="isRateDesc ? 'primary' : 'grey-2'"
          :text-color="isRateDesc ? 'white' : 'grey-8'"
          icon="south"
          aria-label="Sort by rating, highest first"
          @click="sortByRate(true)"
        >
          <q-tooltip>Rating: high → low</q-tooltip>
        </q-btn>
      </q-btn-group>
    </div>

    <MusicTracksListSkeleton v-if="catalog.isArtistTracksLoading"/>
    <q-card v-else class="q-mb-md" flat>
      <q-card-section v-if="catalog.artistTracks.length" class="q-pa-lg">
        <div class="tracks-list q-gutter-xs q-pr-lg">
          <MusicTrackCard
            v-for="track in catalog.artistTracks"
            :key="track.id"
            :track="track"
            :actions="trackActions"
            @play="playTrack(track)"
          />
        </div>

        <div v-if="catalog.artistTracksCursor" class="q-mt-md flex justify-center">
          <q-btn
            color="primary"
            outline
            :loading="catalog.isArtistTracksLoadingMore"
            @click="loadMore"
          >
            Load more
          </q-btn>
        </div>
      </q-card-section>

      <AppNoResultsPlug
        v-else
        title="No tracks found"
        body="Try another rating filter or clear the filter"
      />
    </q-card>
  </div>
</template>

<script lang="ts" setup>
import { computed, watch } from 'vue'
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import MusicTracksListSkeleton from 'src/components/client/Music/MusicTracksListSkeleton.vue'
import MusicTrackRateFilter from 'src/components/client/Music/MusicTrackRateFilter.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { ITrack } from 'src/types'

const props = defineProps<{
  artistId: string
  artistName?: string
}>()

const catalog = useMusicCatalogStore()
const musicPlayer = useMusicPlayer()
const trackActions = ['addToPlaylist']

const isRateAsc = computed(() => (
  catalog.artistTrackListSortField === 'rate' && !catalog.artistTrackListSortDesc
))

const isRateDesc = computed(() => (
  catalog.artistTrackListSortField === 'rate' && catalog.artistTrackListSortDesc
))

const loadTracks = (append = false) => {
  return catalog.getArtistTracks(props.artistId, {
    append,
    fallbackArtist: props.artistName
  })
}

const loadMore = () => {
  return loadTracks(true)
}

const onRatesChange = (rates: number[]): void => {
  void catalog.getArtistTracks(props.artistId, {
    rates,
    fallbackArtist: props.artistName
  })
}

const sortByRate = (desc: boolean): void => {
  void catalog.getArtistTracks(props.artistId, {
    sortField: 'rate',
    sortDesc: desc,
    fallbackArtist: props.artistName
  })
}

const playTrack = (track: ITrack) => {
  musicPlayer.toggleTrack(track, catalog.artistTracks)
}

watch(() => props.artistId, () => {
  loadTracks()
}, { immediate: true })
</script>

<style lang="scss" scoped>
.artist-tracks-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px 12px;
  padding: 10px 12px;
  border-radius: 10px;
  background: rgba(0, 0, 0, 0.03);

  &__rates {
    flex: 1 1 auto;
    min-width: 0;
  }

  &__divider {
    width: 1px;
    height: 28px;
    background: rgba(0, 0, 0, 0.12);
  }

  &__sort {
    flex: 0 0 auto;
    border-radius: 8px;
    overflow: hidden;

    :deep(.q-btn) {
      min-width: 36px;
      padding: 0 8px;
    }
  }
}

@media (max-width: 599px) {
  .artist-tracks-toolbar {
    &__divider {
      display: none;
    }

    &__sort {
      margin-left: auto;
    }
  }
}

.tracks-list {
  max-width: 700px;
  border-right: 1px solid #ccc;
}
</style>
