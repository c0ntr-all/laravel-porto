<template>
  <div class="album-versions q-mb-lg">
    <p class="album-versions__title text-h5 q-mb-lg">Другие версии альбома ({{ versions.meta.count }})</p>
    <q-table
      :rows="versions.data"
      :columns="columns"
      :visible-columns="['image', 'name', 'date']"
      row-key="id"
      hide-header
      hide-bottom
      flat
    >
      <template v-slot:body="props">
        <q-tr
          class="album-versions__row"
          :props="props"
          :key="`m_${props.row.index}`"
          @click="$router.push(`/music/albums/${props.row.id}`)"
        >
          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <template v-if="col.name === 'image'">
              <q-img
                :width="'50px'"
                :src="col.value"
              />
            </template>
            <template v-else-if="col.name === 'name'">
              <div>{{ props.row.name }}</div>
              <MusicAlbumMetaChips
                class="q-mt-xs"
                :album-type="props.row.album_type"
                :edition="props.row.edition"
              />
            </template>
            <template v-else>
              {{ col.value }}
            </template>
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { AlbumVersion } from 'src/components/client/Music/types'
import MusicAlbumMetaChips from 'src/components/client/Music/MusicAlbumMetaChips.vue'
import { IAlbumType } from 'src/types'

interface VersionRow extends AlbumVersion {
  edition?: string | null
  album_type?: IAlbumType | null
  date?: string
}

interface AlbumVersionsProp {
  data: VersionRow[]
  meta: {
    count: number
  }
}

const props = defineProps<{
  versions: AlbumVersionsProp
}>()
const versions = ref(props.versions)
const columns = [{
  name: 'id',
  label: 'id',
  field: 'id'
}, {
  name: 'image',
  label: 'image',
  field: 'image',
  align: 'left' as const,
  style: 'width: 50px'
}, {
  name: 'name',
  label: 'name',
  field: 'name',
  align: 'left' as const,
  sortable: true
}, {
  name: 'date',
  label: 'date',
  field: 'date',
  align: 'left' as const,
  sortable: true,
  format: (val: string) => formatDate(val)
}]

const formatDate = (dateString: string): string => {
  if (!dateString) {
    return ''
  }

  const year = new Date(dateString).getFullYear()
  return Number.isFinite(year) ? String(year) : ''
}
</script>
