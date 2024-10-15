<template>
  <template v-if="loading">
    <q-markup-table>
      <thead>
      <tr>
        <th class="text-left">
          <q-skeleton type="text" width="15px"/>
        </th>
        <th class="text-right">
        </th>
        <th class="text-right">
          <q-skeleton type="text" width="65px"/>
        </th>
        <th class="text-right">
          <q-skeleton type="text" width="65px"/>
        </th>
        <th class="text-right">
          <q-skeleton type="text" width="65px"/>
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="n in 30" :key="n">
        <td class="text-left">
          <q-skeleton type="text" width="15px"/>
        </td>
        <td class="text-right">
          <q-skeleton type="text" width="100px"/>
        </td>
        <td class="text-right">
          <q-skeleton type="text" width="200px"/>
        </td>
        <td class="text-left">
          <q-skeleton type="text" width="200px"/>
        </td>
        <td class="text-right">
          <q-skeleton type="text" width="100px"/>
        </td>
      </tr>
      </tbody>
    </q-markup-table>
  </template>
    <q-table
      :rows="tracks"
      :columns="columns"
      row-key="id"
      :flat="true"
      class="tracks"
      :pagination="{rowsPerPage: 0}"
    >
      <template v-slot:body="props">
        <MusicTrackTableRow @play="initPlay" :row-props="props"/>
      </template>
    </q-table>
</template>
<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import { api } from 'src/boot/axios'
import MusicTrackTableRow from 'src/components/admin/Music/MusicTrackTableRow.vue'
import { IRelationshipItem, ITagShort, ITrack } from 'src/components/admin/Music/types'

interface IResponseTrack {
  id: string
  type: string
  attributes: {
    number: number
    name: string
    image: string
    duration: string
    rate: number
  }
  relationships: {
    tags: {
      data: IRelationshipItem[]
    }
  }
}

interface IGetTracksResponse {
  data: IResponseTrack[]
  included?: any
}

const musicPlayer = useMusicPlayer()
const columns = ref([{
  name: 'number',
  required: true,
  label: '#',
  align: 'left' as const,
  field: (row: ITrack) => row.number,
  sortable: true
}, {
  name: 'id',
  required: true,
  label: 'id',
  align: 'center' as const,
  field: (row: ITrack) => row.id,
  sortable: true,
  style: 'width: 70px'
}, {
  name: 'image',
  required: true,
  label: 'Image',
  align: 'left' as const,
  field: (row: ITrack) => row.image,
  sortable: true,
  style: 'width: 40px'
}, {
  name: 'name',
  required: true,
  label: 'Имя',
  align: 'left' as const,
  field: (row: ITrack) => row.name,
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
}])
const tracks = ref<ITrack[]>([])
const loading = ref(true)

const getTracks = async (): Promise<void> => {
  await api.get<IGetTracksResponse>('v1/music/tracks?include=tags')
    .then(response => {
      tracks.value = response.data.data.map((responseTrack: IResponseTrack) => {
        return {
          id: responseTrack.id,
          name: responseTrack.attributes.name,
          number: responseTrack.attributes.number,
          image: responseTrack.attributes.image,
          artist: '',
          duration: responseTrack.attributes.duration,
          rate: responseTrack.attributes.rate,
          relationships: {
            tags: {
              data: responseTrack.relationships.tags.data.length
                ? getIncluded<ITagShort>('tags', responseTrack.relationships, response.data.included, false) as ITagShort[]
                : []
            }
          }
        }
      })
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      loading.value = false
    })
}

const initPlay = (track: ITrack) => {
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
