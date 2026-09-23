<template>
  <div class="post-form-series-watch q-gutter-sm">
    <div class="text-subtitle2 text-grey-8">Прогресс просмотра</div>
    <div class="row q-col-gutter-sm">
      <div class="col-4">
        <q-input
          v-model.number="local.season"
          type="number"
          min="1"
          step="1"
          label="Сезон"
          outlined
          dense
          :rules="[val => Number(val) >= 1 || 'Минимум 1']"
          @update:model-value="emitChange"
        />
      </div>
      <div class="col-4">
        <q-input
          v-model.number="local.episode_from"
          type="number"
          min="1"
          step="1"
          label="Первая серия"
          outlined
          dense
          :rules="[val => Number(val) >= 1 || 'Минимум 1']"
          @update:model-value="onEpisodeFromChange"
        />
      </div>
      <div class="col-4">
        <q-input
          v-model.number="local.episode_to"
          type="number"
          min="1"
          step="1"
          label="Последняя серия"
          outlined
          dense
          :rules="[
            val => Number(val) >= 1 || 'Минимум 1',
            val => Number(val) >= Number(local.episode_from) || 'Не меньше первой'
          ]"
          @update:model-value="emitChange"
        />
      </div>
    </div>
    <q-input
      v-model="stoppedAtInput"
      label="Остановился на (если не до конца)"
      hint="Время на последней серии, например 00:32 или 00:32:15"
      outlined
      dense
      clearable
      placeholder="00:32:15"
      :rules="[
        val => !val || /^\d{1,2}:\d{2}(:\d{2})?$/.test(String(val).trim()) || 'Формат H:i или H:i:s'
      ]"
      @update:model-value="onStoppedAtChange"
    />
  </div>
</template>

<script lang="ts" setup>
import { reactive, ref, watch } from 'vue'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'
import { emptySeriesWatchProgress } from 'src/utils/LifeLog/seriesWatch'

const props = defineProps<{
  modelValue: ISeriesWatchProgress | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: ISeriesWatchProgress]
}>()

const local = reactive<ISeriesWatchProgress>(
  props.modelValue
    ? { ...props.modelValue }
    : emptySeriesWatchProgress()
)

const stoppedAtInput = ref(props.modelValue?.stopped_at ?? '')

watch(
  () => props.modelValue,
  value => {
    const next = value ?? emptySeriesWatchProgress()
    local.season = next.season
    local.episode_from = next.episode_from
    local.episode_to = next.episode_to
    local.stopped_at = next.stopped_at ?? null
    stoppedAtInput.value = next.stopped_at ?? ''
  },
  { deep: true }
)

function emitChange () {
  emit('update:modelValue', {
    season: Number(local.season) || 1,
    episode_from: Number(local.episode_from) || 1,
    episode_to: Number(local.episode_to) || 1,
    stopped_at: local.stopped_at || null
  })
}

function onEpisodeFromChange () {
  if (Number(local.episode_to) < Number(local.episode_from)) {
    local.episode_to = Number(local.episode_from) || 1
  }
  emitChange()
}

function onStoppedAtChange (value: string | number | null) {
  const raw = String(value ?? '').trim()
  if (!raw || raw === '00:00:00') {
    local.stopped_at = null
    stoppedAtInput.value = ''
  } else {
    local.stopped_at = raw
  }
  emitChange()
}
</script>

<style lang="scss" scoped>
.post-form-series-watch {
  width: 100%;
}
</style>
