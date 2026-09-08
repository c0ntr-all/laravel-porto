<template>
  <q-card
    v-if="progress"
    class="upload-progress q-mb-lg"
    flat
    bordered
  >
    <q-card-section>
      <div class="row items-center no-wrap q-mb-sm">
        <div class="text-subtitle1">{{ title }}</div>
        <q-space />
        <q-badge :color="statusColor" outline>
          {{ statusLabel }}
        </q-badge>
      </div>

      <q-linear-progress
        :value="ratio"
        :indeterminate="indeterminate"
        color="primary"
        rounded
        size="10px"
      />

      <div v-if="progress.message" class="text-caption text-grey-7 q-mt-xs ellipsis">
        {{ progress.message }}
      </div>

      <div class="row q-col-gutter-md q-mt-md">
        <div class="col-12 col-sm-4">
          <div class="upload-progress__stat">
            <div class="upload-progress__stat-label">Tracks</div>
            <div class="upload-progress__stat-value">
              {{ progress.tracks_processed }}
              <span class="upload-progress__stat-total">/ {{ progress.tracks_total }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-4">
          <div class="upload-progress__stat">
            <div class="upload-progress__stat-label">Albums</div>
            <div class="upload-progress__stat-value">
              {{ progress.albums_processed }}
              <span class="upload-progress__stat-total">/ {{ progress.albums_total }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-4">
          <div class="upload-progress__stat">
            <div class="upload-progress__stat-label">Artists</div>
            <div class="upload-progress__stat-value">
              {{ progress.artists_processed }}
              <span class="upload-progress__stat-total">/ {{ progress.artists_total }}</span>
            </div>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useMusicUploadStore } from 'src/stores/modules/musicUploadStore'
import { MusicUploadStatus } from 'src/types'

const store = useMusicUploadStore()

const progress = computed(() => store.activeProgress)

const ratio = computed(() => {
  const total = progress.value?.total || progress.value?.tracks_total || 0
  const processed = progress.value?.processed || progress.value?.tracks_processed || 0
  if (total <= 0) {
    return 0
  }

  return Math.min(1, processed / total)
})

const indeterminate = computed(() => {
  if (!progress.value) {
    return false
  }

  return ['pending', 'running'].includes(progress.value.status)
    && (progress.value.total || 0) <= 0
    && (progress.value.tracks_total || 0) <= 0
})

const title = computed(() => {
  const stage = progress.value?.stage

  if (stage === 'parsing') {
    return 'Reading audio files'
  }
  if (stage === 'scanned') {
    return 'Library scanned'
  }
  if (stage === 'persisting') {
    return 'Saving to library'
  }
  if (stage === 'finished') {
    return progress.value?.status === 'failed' ? 'Upload failed' : 'Upload finished'
  }

  return 'Uploading artist'
})

const statusLabel = computed(() => {
  const labels: Record<MusicUploadStatus, string> = {
    pending: 'Pending',
    running: 'Running',
    completed: 'Completed',
    completed_with_errors: 'With errors',
    failed: 'Failed'
  }

  return labels[progress.value?.status ?? 'pending']
})

const statusColor = computed(() => {
  const colors: Record<MusicUploadStatus, string> = {
    pending: 'grey',
    running: 'info',
    completed: 'positive',
    completed_with_errors: 'warning',
    failed: 'negative'
  }

  return colors[progress.value?.status ?? 'pending']
})
</script>

<style lang="scss" scoped>
.upload-progress {
  &__stat-label {
    color: #5c6773;
    font-size: 12px;
    line-height: 16px;
  }

  &__stat-value {
    font-size: 20px;
    font-weight: 600;
    line-height: 28px;
  }

  &__stat-total {
    color: #5c6773;
    font-size: 16px;
    font-weight: 400;
  }
}
</style>
