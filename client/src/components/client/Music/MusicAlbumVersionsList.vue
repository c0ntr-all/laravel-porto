<template>
  <div class="album-versions q-mb-lg">
    <p class="album-versions__title text-h5 q-mb-lg">Другие версии альбома ({{ versions.meta.count }})</p>
    <q-table
      :card-container-style="'background-color: #ff0000'"
      :rows="versions.data"
      :columns="columns"
      :visible-columns="['image', 'name', 'date']"
      row-key="name"
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
import { useRouter } from 'vue-router'
import { AlbumVersion } from 'src/components/client/Music/types'

interface AlbumVersionsProp {
  data: AlbumVersion[],
  meta: {
    count: number
  }
}

const $router = useRouter()
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
  format: (val: any) => formatDate(val)
}]

const formatDate = (dateString: string): number => {
  return new Date(dateString).getFullYear()
}

</script>

<style scoped lang="scss">

</style>
