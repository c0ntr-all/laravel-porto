<template>
  <div class="music-player-expanded">
    <div class="music-player-expanded__controls">
      <div class="music-player-expanded__now-playing">
        <q-img
          v-if="player.currentTrack?.image"
          :src="player.currentTrack.image"
          class="music-player-expanded__cover"
          :alt="player.currentTrack.name"
          ratio="1"
          fit="cover"
        />
        <div v-else class="music-player-expanded__cover music-player-expanded__cover--empty">
          <q-icon name="music_note" size="24px" color="grey-5" />
        </div>

        <div class="music-player-expanded__meta">
          <div class="music-player-expanded__name">
            {{ player.currentTrack?.name || 'No track selected' }}
          </div>
          <div class="music-player-expanded__artist">
            {{ player.currentTrack?.artist || 'Unknown artist' }}
          </div>
        </div>
      </div>

      <div class="music-player-expanded__transport">
        <q-btn
          icon="skip_previous"
          flat
          dense
          round
          :disable="!player.hasTrack"
          @click="player.previous()"
        />
        <q-btn
          :icon="player.isPlaying ? 'pause' : 'play_arrow'"
          flat
          dense
          round
          :loading="player.status === 'loading'"
          :disable="!player.hasTrack && !player.playlist.length"
          @click="player.toggle()"
        />
        <q-btn
          icon="stop"
          flat
          dense
          round
          :disable="!player.hasTrack"
          @click="player.stop()"
        />
        <q-btn
          icon="skip_next"
          flat
          dense
          round
          :disable="!player.hasTrack"
          @click="player.next()"
        />
      </div>

      <div class="music-player-expanded__seek">
        <div class="music-player-expanded__time">{{ player.timePassed }}</div>
        <AppSlider
          v-model="progress"
          :disable="!player.hasTrack || player.duration <= 0"
          only-drop
        />
        <div class="music-player-expanded__time">{{ player.timeTotal }}</div>
      </div>

      <div class="music-player-expanded__extra">
        <div class="music-player-expanded__modes">
          <q-btn
            icon="shuffle"
            flat
            dense
            round
            :color="player.shuffleEnabled ? 'primary' : 'grey-7'"
            @click="player.toggleShuffle()"
          >
            <q-tooltip>Shuffle</q-tooltip>
          </q-btn>
          <q-btn
            :icon="player.repeatMode === 'one' ? 'repeat_one' : 'repeat'"
            flat
            dense
            round
            :color="player.repeatMode !== 'off' ? 'primary' : 'grey-7'"
            @click="player.cycleRepeat()"
          >
            <q-tooltip>{{ repeatLabel }}</q-tooltip>
          </q-btn>
        </div>

        <div class="music-player-expanded__volume">
          <q-btn
            :icon="volumeIcon"
            flat
            dense
            round
            @click="player.toggleMute()"
          />
          <AppSlider
            v-model="volume"
            :disable="player.muted"
            width="88px"
            :step="1"
          />
        </div>
      </div>
    </div>

    <q-separator />

    <q-scroll-area class="music-player-expanded__playlist">
      <div v-if="player.playlist.length" class="q-pa-md q-gutter-xs">
        <MusicTrackCard
          v-for="track in player.playlist"
          :key="track.id"
          :track="track"
          @play="player.toggleTrack(track)"
        />
      </div>
      <div v-else class="text-grey-6 q-pa-lg text-center">
        Playlist is empty
      </div>
    </q-scroll-area>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import AppSlider from 'src/components/default/AppSlider.vue'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'

const player = useMusicPlayer()

const progress = computed({
  get: () => player.progress,
  set: value => player.seekToProgress(value)
})

const volume = computed({
  get: () => player.volumePercent,
  set: value => player.setVolumePercent(value)
})

const volumeIcon = computed(() => {
  if (player.muted || player.volumePercent === 0) {
    return 'volume_off'
  }

  if (player.volumePercent < 40) {
    return 'volume_down'
  }

  return 'volume_up'
})

const repeatLabel = computed(() => {
  if (player.repeatMode === 'one') {
    return 'Repeat one'
  }

  if (player.repeatMode === 'all') {
    return 'Repeat all'
  }

  return 'Repeat off'
})
</script>

<style lang="scss" scoped>
.music-player-expanded {
  display: flex;
  flex-direction: column;
  width: 660px;
  min-height: 500px;
  max-height: min(72vh, 720px);

  &__controls {
    position: sticky;
    top: 0;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px 16px 10px;
    background: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  }

  &__now-playing {
    display: flex;
    align-items: center;
    min-width: 0;
  }

  &__cover {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 8px;
    overflow: hidden;

    &--empty {
      display: flex;
      align-items: center;
      justify-content: center;
      background: #eceff3;
    }
  }

  &__meta {
    min-width: 0;
    margin-left: 12px;
  }

  &__name,
  &__artist {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 12.5px;
    line-height: 16px;
  }

  &__artist {
    font-weight: 700;
  }

  &__transport {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__extra,
  &__volume,
  &__modes {
    display: flex;
    align-items: center;
  }

  &__seek {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  &__time {
    flex-shrink: 0;
    min-width: 2.5em;
    color: #818c99;
    font-size: 12px;
  }

  &__extra {
    justify-content: space-between;
  }

  &__volume {
    flex: 1;
    justify-content: flex-end;
    gap: 4px;
    margin-left: 8px;
  }

  &__playlist {
    flex: 1;
    height: 340px;
  }
}
</style>
