<template>
  <MusicTracksListSkeleton v-if="historyStore.isLoading" />

  <q-card v-else class="q-mb-md" flat>
    <q-card-section v-if="historyGroups.length" class="q-pa-lg">
      <div class="history-list">
        <section
          v-for="group in historyGroups"
          :key="group.day || 'unknown'"
          class="history-day"
        >
          <header class="history-day__header">
            <h3 class="history-day__title">{{ group.label }}</h3>
            <span class="history-day__count">
              {{ group.items.length }}
              {{ group.items.length === 1 ? 'play' : 'plays' }}
            </span>
          </header>

          <div class="history-day__items q-gutter-md">
            <div
              v-for="item in group.items"
              :key="item.id"
              class="history-row"
            >
              <time class="history-row__time" :datetime="item.created_at || undefined">
                {{ formatHistoryTime(item.created_at) || '—' }}
              </time>
              <div class="history-row__track">
                <MusicTrackCard
                  v-if="item.track"
                  :track="item.track"
                  :actions="trackActions"
                  @play="playTrack(item.track)"
                />
              </div>
            </div>
          </div>
        </section>
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
import { formatHistoryTime, groupHistoryByDay } from 'src/utils/musicHistory'
import { ITrack } from 'src/types'

const historyStore = useMusicHistoryStore()
const musicPlayer = useMusicPlayer()
const trackActions = ['addToPlaylist']

const historyItems = computed(() => (
  historyStore.items.filter((item): item is typeof item & { track: ITrack } => Boolean(item.track))
))

const historyGroups = computed(() => groupHistoryByDay(historyItems.value))

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
.history-list {
  max-width: 760px;
}

.history-day {
  & + & {
    margin-top: 28px;
  }

  &__header {
    display: flex;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fff;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    letter-spacing: 0.01em;
  }

  &__count {
    color: #757575;
    font-size: 0.8rem;
  }
}

.history-row {
  display: grid;
  grid-template-columns: 52px minmax(0, 1fr);
  gap: 12px;
  align-items: start;

  &__time {
    margin-top: 18px;
    color: #757575;
    font-size: 0.8rem;
    font-variant-numeric: tabular-nums;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    line-height: 1.2;
  }

  &__track {
    min-width: 0;
  }
}

.history-list-sentinel {
  width: 100%;
  height: 1px;
}

@media (max-width: 599px) {
  .history-row {
    grid-template-columns: 44px minmax(0, 1fr);
    gap: 8px;
  }

  .history-row__time {
    margin-top: 14px;
    font-size: 0.75rem;
  }
}
</style>
