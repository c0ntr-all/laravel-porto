<template>
  <div class="playing-settings">
    <div class="text-h6 q-mb-xs">Playing</div>
    <div class="text-caption text-grey-7 q-mb-lg">
      Playback rules for scrobbling tracks into listening history.
    </div>

    <q-card flat bordered class="playing-settings__card">
      <q-card-section>
        <div class="playing-settings__label">
          Скробблить на
          <span class="playing-settings__value">{{ scrobblePercent }}%</span>
          композиции
        </div>
        <div class="text-caption text-grey-7 q-mb-md">
          Сколько процентов трека нужно прослушать, чтобы он добавился в историю прослушивания.
          Минимум {{ minPercent }}%.
        </div>

        <q-slider
          :model-value="scrobblePercent"
          :min="minPercent"
          :max="maxPercent"
          :step="1"
          label
          label-always
          color="primary"
          @update:model-value="onPercentChange"
        />

        <div class="playing-settings__scale text-caption text-grey-6">
          <span>{{ minPercent }}%</span>
          <span>{{ maxPercent }}%</span>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import {
  MUSIC_SCROBBLE_PERCENT_MAX,
  MUSIC_SCROBBLE_PERCENT_MIN,
  useSettingsStore
} from 'src/stores/modules/settingsStore'

const settingsStore = useSettingsStore()
const { settings } = storeToRefs(settingsStore)

const minPercent = MUSIC_SCROBBLE_PERCENT_MIN
const maxPercent = MUSIC_SCROBBLE_PERCENT_MAX

const scrobblePercent = computed(() => settings.value.music.scrobblePercent)

const onPercentChange = (value: number | null) => {
  if (value == null) {
    return
  }

  settingsStore.updateMusic({ scrobblePercent: value })
}
</script>

<style lang="scss" scoped>
.playing-settings {
  max-width: 560px;

  &__card {
    border-radius: 12px;
  }

  &__label {
    margin-bottom: 6px;
    font-size: 15px;
    font-weight: 600;
    color: #282f53;
  }

  &__value {
    color: $primary;
  }

  &__scale {
    display: flex;
    justify-content: space-between;
    margin-top: 4px;
  }
}
</style>
