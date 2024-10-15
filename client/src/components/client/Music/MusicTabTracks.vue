<template>
  <q-card class="q-mb-md" flat bordered>
    <q-card-section>
      <TracksFilter @submitFilter="getTracks"/>
    </q-card-section>
  </q-card>

  <MusicTabTracksSkeleton v-if="loading"/>

  <template v-else>
    <q-table
      :rows="tracks"
      :columns="columns"
      row-key="name"
      :flat="true"
      :rows-per-page-options="[0]"
      :v-model:pagination="pagination"
      class="tracks"
    >
      <template v-slot:body="props">
        <track-card-row :props="props" @play="initPlay"/>
      </template>
    </q-table>
  </template>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useQuasar } from 'quasar'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { api } from 'src/boot/axios'
import TracksFilter from 'src/components/client/Music/default/tracksTab/TracksFilter.vue'
import TrackCardRow from 'src/components/client/Music/default/tracksTab/TrackCardRow.vue'
import MusicTabTracksSkeleton from 'src/components/client/Music/MusicTabTracksSkeleton.vue'
import { ITrack } from 'src/components/client/Music/types'

interface Pagination {
  perPage: number
  hasPages: boolean
  nextPageUrl: string
  prevPageUrl: string
}

const props = withDefaults(defineProps<{ tracksUrl: string }>(), {
  tracksUrl: 'music/tracks'
})

const $q = useQuasar()
const musicPlayer = useMusicPlayer()

const columns = ref([
  {
    name: 'number',
    required: true,
    label: '#',
    align: 'center' as const,
    field: (row: ITrack) => row.number,
    sortable: true,
    style: 'width: 70px'
  }, {
    name: 'rate',
    required: true,
    label: '',
    align: 'center' as const,
    field: (row: ITrack) => row.rate,
    sortable: true,
    style: 'width: 120px'
  }, {
    name: 'name',
    required: true,
    label: 'Name',
    align: 'left' as const,
    field: (row: ITrack) => row.name,
    sortable: true
  }, {
    name: 'artist',
    required: true,
    label: 'Artist',
    align: 'left' as const,
    field: (row: ITrack) => row.artist,
    sortable: true
  }, {
    name: 'tags',
    required: true,
    label: 'Tags',
    align: 'left' as const,
    field: (row: ITrack) => row.relationships.tags.data,
    sortable: true
  }, {
    name: 'duration',
    required: true,
    label: 'Duration',
    align: 'right' as const,
    field: (row: ITrack) => row.duration,
    sortable: true,
    style: 'width: 130px'
  }
])
const tracks = ref<ITrack[]>([])
const loading = ref(true)

const pagination = ref<Pagination>({
  perPage: 0,
  hasPages: false,
  nextPageUrl: '',
  prevPageUrl: ''
})

const getTracks = async (filters?: Record<string, unknown>): Promise<void> => {
  filters = filters || {}

  const data = new FormData()
  data.append('filters', JSON.stringify(filters))

  await api.post(props.tracksUrl, {
    filters,
    with_tags: true
  }).then(response => {
    tracks.value = response.data.tracks
    pagination.value = response.data.pagination

    loading.value = false
  }).catch((error: any) => {
    $q.notify({
      type: 'negative',
      message: error.response.data.message || 'Error!'
    })
  })
}

const initPlay = (track: ITrack) => {
  // Replacing playlist with new track
  if (!musicPlayer.playlist.includes(track)) {
    musicPlayer.setPlaylist(tracks.value)
  }
  musicPlayer.playTrack(track)
}

onMounted(() => {
  getTracks()
})
</script>
<style lang="scss" scoped>

</style>
