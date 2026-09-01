<template>
  <q-table
    class="uploads-table"
    :rows="store.uploads"
    :columns="columns"
    row-key="id"
    :loading="store.isUploadsLoading"
    :pagination="pagination"
    flat
    bordered
    hide-pagination
    no-data-label="No upload sessions yet"
  >
    <template #header="props">
      <q-tr :props="props">
        <q-th auto-width />
        <q-th
          v-for="col in props.cols"
          :key="col.name"
          :props="props"
        >
          {{ col.label }}
        </q-th>
      </q-tr>
    </template>

    <template #body="props">
      <q-tr
        :props="props"
        class="cursor-pointer"
        @click="toggleExpand(props)"
      >
        <q-td auto-width>
          <q-btn
            size="sm"
            dense
            round
            flat
            :icon="props.expand ? 'expand_less' : 'expand_more'"
            @click.stop="toggleExpand(props)"
          />
        </q-td>
        <q-td
          v-for="col in props.cols"
          :key="col.name"
          :props="props"
        >
          <template v-if="col.name === 'status'">
            <q-badge :color="statusColor(props.row.status)">
              {{ statusLabel(props.row.status) }}
            </q-badge>
          </template>
          <template v-else-if="col.name === 'source_path'">
            <div class="uploads-table__path" :title="props.row.source_path">
              {{ props.row.source_path }}
            </div>
          </template>
          <template v-else-if="col.name === 'tracks'">
            {{ props.row.tracks_created }}/{{ props.row.tracks_found }}
          </template>
          <template v-else-if="col.name === 'actions'">
            <q-btn
              icon="delete"
              color="negative"
              flat
              round
              dense
              @click.stop="confirmDelete(props.row)"
            >
              <q-tooltip>Delete session log</q-tooltip>
            </q-btn>
          </template>
          <template v-else>
            {{ col.value }}
          </template>
        </q-td>
      </q-tr>
      <q-tr v-show="props.expand" :props="props">
        <q-td colspan="100%" class="q-pa-none">
          <MusicUploadSessionDetails
            :upload="props.row"
            :loading="store.isDetailsLoading(props.row.id)"
          />
        </q-td>
      </q-tr>
    </template>
  </q-table>

  <div v-if="store.uploadsCursor" class="q-mt-md flex justify-center">
    <q-btn
      color="primary"
      outline
      :loading="store.isUploadsLoadingMore"
      @click="store.getUploads({ append: true })"
    >
      Load more
    </q-btn>
  </div>
</template>

<script lang="ts" setup>
import { onMounted } from 'vue'
import { useQuasar, type QTableColumn } from 'quasar'
import { useMusicUploadStore } from 'src/stores/modules/musicUploadStore'
import { humanDatetime } from 'src/utils/datetime'
import MusicUploadSessionDetails from 'src/components/admin/Music/MusicUploadSessionDetails.vue'
import { IMusicUpload, MusicUploadStatus } from 'src/types'

type ExpandableRow = {
  expand: boolean
  row: IMusicUpload
}

const $q = useQuasar()
const store = useMusicUploadStore()

const pagination = {
  rowsPerPage: 0
}

const columns: QTableColumn<IMusicUpload>[] = [
  {
    name: 'created_at',
    label: 'Date',
    field: 'created_at',
    align: 'left',
    format: (value: string | null) => value ? humanDatetime(value) : '—'
  },
  {
    name: 'artist_name',
    label: 'Artists',
    field: 'artist_name',
    align: 'left',
    format: (value: string | null) => value || '—'
  },
  {
    name: 'source_path',
    label: 'Path',
    field: 'source_path',
    align: 'left'
  },
  {
    name: 'status',
    label: 'Status',
    field: 'status',
    align: 'left'
  },
  {
    name: 'tracks',
    label: 'Tracks',
    field: 'tracks_created',
    align: 'right'
  },
  {
    name: 'albums_created',
    label: 'Albums',
    field: 'albums_created',
    align: 'right'
  },
  {
    name: 'duration_ms',
    label: 'Duration',
    field: 'duration_ms',
    align: 'right',
    format: (value: number | null) => formatDurationMs(value)
  },
  {
    name: 'actions',
    label: '',
    field: 'id',
    align: 'right'
  }
]

const statusColor = (status: MusicUploadStatus): string => {
  const colors: Record<MusicUploadStatus, string> = {
    pending: 'grey',
    running: 'info',
    completed: 'positive',
    completed_with_errors: 'warning',
    failed: 'negative'
  }

  return colors[status]
}

const statusLabel = (status: MusicUploadStatus): string => {
  const labels: Record<MusicUploadStatus, string> = {
    pending: 'Pending',
    running: 'Running',
    completed: 'Completed',
    completed_with_errors: 'With errors',
    failed: 'Failed'
  }

  return labels[status]
}

const formatDurationMs = (ms: number | null): string => {
  if (ms == null || Number.isNaN(ms)) {
    return '—'
  }

  const seconds = Math.round(ms / 1000)
  if (seconds < 60) {
    return `${seconds}s`
  }

  const minutes = Math.floor(seconds / 60)
  const rest = seconds % 60

  return `${minutes}m ${rest}s`
}

const toggleExpand = (props: ExpandableRow) => {
  props.expand = !props.expand

  if (props.expand) {
    void store.getUpload(props.row.id)
  }
}

const confirmDelete = (upload: IMusicUpload) => {
  $q.dialog({
    title: 'Delete upload session',
    message: 'This removes the session log only. Catalog artists, albums and tracks stay in the library.',
    cancel: true
  }).onOk(() => {
    void store.deleteUpload(upload.id)
  })
}

onMounted(() => {
  void store.getUploads()
})
</script>

<style lang="scss" scoped>
.uploads-table {
  &__path {
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
</style>
