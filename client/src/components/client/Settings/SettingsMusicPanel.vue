<template>
  <div class="settings-panel">
    <div class="settings-panel__intro">
      <div class="settings-panel__title">Music</div>
      <p class="settings-panel__text">
        Параметры плеера: громкость, скробблинг, повтор и перемешивание очереди.
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
          @update:model-value="onVolumePercent"
        />
        <span class="settings-panel__volume-value">{{ Math.round(player.volumePercent) }}%</span>
      </div>
    </div>

    <div class="settings-panel__block">
      <div class="settings-panel__label">
        Скробблить на
        <span class="settings-panel__accent">{{ scrobblePercent }}%</span>
        композиции
      </div>
      <p class="settings-panel__hint">
        Сколько процентов трека нужно прослушать, чтобы он добавился в историю.
        Минимум {{ minPercent }}%.
      </p>
      <q-slider
        :model-value="scrobblePercent"
        :min="minPercent"
        :max="maxPercent"
        :step="1"
        label
        color="primary"
        @update:model-value="onScrobblePercent"
      />
      <div class="settings-panel__scale">
        <span>{{ minPercent }}%</span>
        <span>{{ maxPercent }}%</span>
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
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import {
  MUSIC_SCROBBLE_PERCENT_MAX,
  MUSIC_SCROBBLE_PERCENT_MIN,
  useSettingsStore
} from 'src/stores/modules/settingsStore'

const player = useMusicPlayer()
const settingsStore = useSettingsStore()
const { settings } = storeToRefs(settingsStore)

const minPercent = MUSIC_SCROBBLE_PERCENT_MIN
const maxPercent = MUSIC_SCROBBLE_PERCENT_MAX
const scrobblePercent = computed(() => settings.value.music.scrobblePercent)

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

const onVolumePercent = (value: number | null) => {
  if (value == null) {
    return
  }

  player.setVolumePercent(value)
}

const onScrobblePercent = (value: number | null) => {
  if (value == null) {
    return
  }

  settingsStore.updateMusic({ scrobblePercent: value })
}
</script>

<style lang="scss" scoped>
.settings-panel__accent {
  color: $primary;
}

.settings-panel__hint {
  margin: 0 0 10px;
  color: #777a8f;
  font-size: 13px;
  line-height: 1.45;
}

.settings-panel__scale {
  display: flex;
  justify-content: space-between;
  margin-top: 4px;
  color: #777a8f;
  font-size: 12px;
}
</style>
