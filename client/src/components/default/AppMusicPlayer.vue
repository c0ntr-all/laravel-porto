<template>
  <div class="music-player">
    <div class="music-player__buttons" @click.stop>
      <q-btn
        icon="skip_previous"
        color="primary"
        flat
        round
        dense
        :disable="!player.hasTrack"
        @click="player.previous()"
      />
      <q-btn
        :icon="player.isPlaying ? 'pause' : 'play_arrow'"
        color="primary"
        flat
        round
        dense
        :loading="player.status === 'loading'"
        :disable="!player.hasTrack && !player.playlist.length"
        @click="player.toggle()"
      />
      <q-btn
        icon="skip_next"
        color="primary"
        flat
        round
        dense
        :disable="!player.hasTrack"
        @click="player.next()"
      />
    </div>

    <div class="music-player__title text-primary">
      <template v-if="player.currentTrack">
        <span class="text-bold">{{ player.currentArtist }}</span>
        <span> - {{ player.currentTrack.name }}</span>
      </template>
      <span v-else>No track selected</span>
    </div>

    <div class="music-player__queue">
      <q-icon name="queue_music" color="primary" size="20px" />
      <q-badge
        v-if="player.playlist.length"
        floating
        color="primary"
        :label="player.playlist.length"
      />
    </div>

    <q-menu
      class="music-player__menu"
      anchor="bottom left"
      self="top left"
      :offset="[0, 8]"
      max-height="80vh"
      transition-show="jump-down"
      transition-hide="jump-up"
    >
      <AppMusicPlayerExpanded />
    </q-menu>
  </div>
</template>

<script lang="ts" setup>
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import AppMusicPlayerExpanded from 'src/components/default/AppMusicPlayerExpanded.vue'

const player = useMusicPlayer()
</script>

<style lang="scss" scoped>
.music-player {
  display: flex;
  align-items: center;
  min-width: 0;
  margin-left: 16px;
  padding: 4px 12px 4px 4px;
  border-radius: 8px;
  cursor: pointer;

  &:hover {
    background: rgba(174, 183, 194, 0.12);
  }

  &__buttons {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    cursor: default;
  }

  &__title {
    flex: 1;
    min-width: 0;
    max-width: 360px;
    margin-left: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 14px;
    line-height: 20px;
  }

  &__queue {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    margin-left: 4px;
  }
}
</style>
