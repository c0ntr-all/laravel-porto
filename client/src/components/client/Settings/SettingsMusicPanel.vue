<template>
  <div class="settings-panel">
    <div class="settings-panel__intro">
      <div class="settings-panel__title">Music</div>
      <p class="settings-panel__text">
        Параметры плеера: громкость, повтор и перемешивание очереди.
      </p>
    </div>

    <div class="settings-panel__block">
      <div class="settings-panel__label">Громкость</div>
      <div class="settings-panel__volume">
        <q-btn
          flat
          round
          dense
          :icon="player.muted ? 'volume_off' : 'volume_up'"
          color="primary"
          @click="player.toggleMute()"
        />
        <q-slider
          :model-value="player.volumePercent"
          :min="0"
          :max="100"
          color="primary"
          @update:model-value="player.setVolumePercent($event)"
        />
        <span class="settings-panel__volume-value">{{ Math.round(player.volumePercent) }}%</span>
      </div>
    </div>

    <q-list class="settings-panel__list" separator>
      <q-item tag="label">
        <q-item-section>
          <q-item-label>Перемешивание</q-item-label>
          <q-item-label caption>Случайный порядок треков в очереди</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-toggle
            :model-value="player.shuffleEnabled"
            color="primary"
            @update:model-value="onShuffle"
          />
        </q-item-section>
      </q-item>
    </q-list>

    <div class="settings-panel__block">
      <div class="settings-panel__label">Повтор</div>
      <q-btn-toggle
        :model-value="player.repeatMode"
        no-caps
        unelevated
        toggle-color="primary"
        color="grey-3"
        text-color="grey-8"
        :options="repeatOptions"
        @update:model-value="player.setRepeat($event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'

const player = useMusicPlayer()

const repeatOptions = [
  { label: 'Выкл', value: 'off', icon: 'repeat' },
  { label: 'Все', value: 'all', icon: 'repeat' },
  { label: 'Трек', value: 'one', icon: 'repeat_one' }
]

const onShuffle = (enabled: boolean) => {
  if (player.shuffleEnabled !== enabled) {
    player.toggleShuffle()
  }
}
</script>
