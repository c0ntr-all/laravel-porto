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
            {{ player.currentArtist || 'Unknown artist' }}
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
          :buffered="player.bufferedPercents"
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
          />
          <q-btn
            :icon="player.repeatMode === 'one' ? 'repeat_one' : 'repeat'"
            flat
            dense
            round
            :color="player.repeatMode !== 'off' ? 'primary' : 'grey-7'"
            @click="player.cycleRepeat()"
          />
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

    <div class="music-player-expanded__playlist-head">
      Current playlist
      <span v-if="player.playlist.length" class="music-player-expanded__playlist-count">
        {{ player.playlist.length }}
      </span>
    </div>

    <div class="music-player-expanded__playlist">
      <button
        v-for="(track, index) in player.playlist"
        :key="track.id"
        type="button"
        class="music-player-expanded__track"
        :class="{ 'music-player-expanded__track--current': player.isCurrentTrack(track.id) }"
        @click="player.toggleTrack(track)"
      >
        <span class="music-player-expanded__track-index">{{ index + 1 }}</span>
        <span class="music-player-expanded__track-body">
          <span class="music-player-expanded__track-name">{{ track.name }}</span>
          <span class="music-player-expanded__track-artist">{{ trackArtist(track) }}</span>
        </span>
        <span class="music-player-expanded__track-time">{{ track.duration }}</span>
      </button>
      <div v-if="!player.playlist.length" class="music-player-expanded__empty">
        Playlist is empty
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import AppSlider from 'src/components/default/AppSlider.vue'
import { formatTrackArtist } from 'src/api/mappers/Music/track.mapper'
import { ITrack } from 'src/types'

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

const trackArtist = (track: ITrack): string => {
  return formatTrackArtist(track) || 'Unknown artist'
}
</script>

<style lang="scss" scoped>
.music-player-expanded {
  display: flex;
  flex-direction: column;
  width: 560px;
  max-width: 90vw;
  max-height: min(72vh, 720px);

  &__controls {
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    gap: 8px;
    padding: 12px 16px 10px;
    background: #fff;
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

  &__playlist-head {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    gap: 8px;
    padding: 10px 16px 8px;
    color: #4a5563;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  &__playlist-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 9px;
    background: rgba(25, 118, 210, 0.12);
    color: #1976d2;
    font-size: 11px;
  }

  &__playlist {
    flex: 1 1 auto;
    min-height: 180px;
    overflow: auto;
    padding: 0 8px 8px;
  }

  &__empty {
    padding: 24px 16px;
    color: #818c99;
    text-align: center;
  }

  &__track {
    display: flex;
    align-items: center;
    width: 100%;
    min-width: 0;
    padding: 8px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    text-align: left;
    cursor: pointer;

    &:hover,
    &--current {
      background: rgba(174, 183, 194, 0.12);
    }

    &--current .music-player-expanded__track-name {
      color: #1976d2;
      font-weight: 700;
    }
  }

  &__track-index {
    flex-shrink: 0;
    width: 24px;
    color: #818c99;
    font-size: 12px;
    text-align: center;
  }

  &__track-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
    margin: 0 8px;
  }

  &__track-name,
  &__track-artist {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__track-name {
    font-size: 13px;
    line-height: 16px;
  }

  &__track-artist {
    color: #818c99;
    font-size: 12px;
    line-height: 16px;
  }

  &__track-time {
    flex-shrink: 0;
    color: #818c99;
    font-size: 12px;
  }
}
</style>
