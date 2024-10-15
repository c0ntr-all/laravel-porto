<template>
  <template v-if="loading">
  </template>
  <template v-else>
    <q-table
      :rows="albums"
      :columns="columns"
      row-key="name"
      :flat="true"
      :rows-per-page-options="[0]"
    >
      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            {{ col.label }}
          </q-th>
          <q-th auto-width/>
        </q-tr>
      </template>
      <template v-slot:body="props">
        <q-tr :props="props" class="album-row">
          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div v-if="col.name === 'image'" class="album-row__image">
              <img :src="col.value" :alt="col.value">
            </div>
            <div v-else-if="col.name === 'tags'" class="album-row__tags">
              <div class="album-row__tag">
                <q-chip v-for="tag in col.value.items" :key="tag.id">{{ tag.name }}</q-chip>
              </div>
            </div>
            <span v-else>{{ col.value }}</span>
          </q-td>
          <q-td class="q-gutter-x-sm" auto-width>
            <q-btn size="sm" label="Редактировать"/>
            <q-btn size="sm" label="Удалить" color="red"/>
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </template>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { api } from 'src/boot/axios'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import { ITagShort, IAlbum, IGetAlbumsResponse, IResponseAlbum } from 'src/components/admin/Music/types'

const columns = ref([{
  name: 'image',
  required: true,
  label: 'Изображение',
  align: 'center' as const,
  field: (row: IAlbum) => row.image,
  sortable: false,
  style: 'width: 60px'
}, {
  name: 'year',
  required: true,
  label: 'Год',
  align: 'left' as const,
  field: (row: IAlbum) => row.year,
  sortable: true,
  style: 'width: 60px'
}, {
  name: 'name',
  required: true,
  label: 'Имя',
  align: 'left' as const,
  field: (row: IAlbum) => row.name,
  sortable: true,
  style: 'width: 60px'
}, {
  name: 'artist_name',
  required: true,
  label: 'Исполнитель',
  align: 'left' as const,
  field: (row: IAlbum) => row.artist_name,
  sortable: true
}, {
  name: 'tags',
  required: true,
  label: 'Теги',
  align: 'center' as const,
  field: (row: IAlbum) => row.relationships.tags.data,
  sortable: false
}])
const loading = ref(false)
const albums = ref<IAlbum[]>([])

const getAlbums = async (): Promise<void> => {
  await api.get<IGetAlbumsResponse>('v1/music/albums')
    .then(response => {
      albums.value = response.data.data.map((responseAlbum: IResponseAlbum) => {
        return {
          id: responseAlbum.id,
          image: responseAlbum.attributes.image,
          year: responseAlbum.attributes.year,
          name: responseAlbum.attributes.name,
          artist_name: responseAlbum.attributes.artist_name,
          relationships: {
            tags: {
              data: getIncluded<ITagShort>('tags', responseAlbum.relationships, response.data.included) as ITagShort[]
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

onMounted(() => {
  getAlbums()
})
</script>

<style lang="scss" scoped>
.album-row {
  &__image {
    width: 50px;
    height: 50px;
    overflow: hidden;

    img {
      width: 100%;
      object-fit: cover;
    }
  }
}
</style>
