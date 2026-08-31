<template>
  <MusicTracksListSkeleton v-if="historyStore.isLoading" />

  <q-card v-else class="q-mb-md" flat>
    <q-card-section v-if="historyItems.length" class="q-pa-lg">
      <div class="tracks-list q-gutter-md q-pr-lg">
        <div
          v-for="item in historyItems"
          :key="item.id"
        >
          <div v-if="item.created_at" class="text-caption text-grey-6 q-mb-xs">
            {{ item.created_at }}
          </div>
          <MusicTrackCard
            :track="item.track"
            :actions="trackActions"
            @play="playTrack(item.track)"
          />
        </div>
      </div>

      <div
        v-if="historyStore.hasMore"
        ref="sentinel"
        class="history-list-sentinel"
      />
      <div
        v-if="historyStore.isLoadingMore"
        class="flex justify-center q-my-md"
      >
        <q-spinner color="primary" size="2em" />
      </div>
    </q-card-section>

    <AppNoResultsPlug
      v-else
      title="No listening history yet"
      body="Played tracks will show up here"
    />
  </q-card>
</template>

<script lang="ts" setup>
import { computed, onMounted } from 'vue'
import { useMusicHistoryStore } from 'src/stores/modules/musicHistoryStore'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import MusicTracksListSkeleton from 'src/components/client/Music/MusicTracksListSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { ITrack } from 'src/types'

const historyStore = useMusicHistoryStore()
const musicPlayer = useMusicPlayer()
const trackActions = ['addToPlaylist']

const historyItems = computed(() => (
  historyStore.items.filter((item): item is typeof item & { track: ITrack } => Boolean(item.track))
))

const playTrack = (track: ITrack) => {
  musicPlayer.toggleTrack(track, historyItems.value.map(item => item.track))
}

const { sentinel } = useScrollSentinel(
  () => { void historyStore.getHistory({ append: true }) },
  () => historyStore.hasMore && !historyStore.isLoading && !historyStore.isLoadingMore
)

onMounted(() => {
  void historyStore.getHistory()
})
</script>

<style lang="scss" scoped>
.tracks-list {
  max-width: 700px;
  border-right: 1px solid #ccc;
}

.history-list-sentinel {
  width: 100%;
  height: 1px;
}
</style>
