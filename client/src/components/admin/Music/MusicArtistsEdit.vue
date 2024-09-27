<template>
  <div class="text-h6 q-mb-md">Artists edit</div>
  <div class="q-mb-md">
    Total artists: <b>{{ total }}</b>
  </div>
  <div class="artists-search q-mb-md">
    <q-input
      v-model="search"
      @keyup.enter="getArtists(search)"
      label="Search"
      maxlength="12"
      dense
      outlined
      bottom-slots
      counter
    >
      <template v-slot:append>
        <q-icon v-if="search !== ''" name="close" @click="search = ''" class="cursor-pointer"/>
      </template>
      <template v-slot:after>
        <q-icon name="search" @click="getArtists(search)" class="cursor-pointer"/>
      </template>
    </q-input>
  </div>
  <div class="artists-list">
    <q-table
      :rows="artists"
      :columns="columns"
      row-key="name"
      :flat="true"
      :pagination="{rowsPerPage: 0}"
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
        <q-tr :props="props" class="artist-row">
          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div v-if="col.name === 'image'" class="artist-row__image">
              <img :src="col.value" :alt="col.value">
            </div>
            <div v-else-if="col.name === 'tags'" class="artist-row__tags">
              <div class="artist-row__tag">
                <span
                  v-for="tag in col.value.filter((tag: Tag) => tag.is_base)"
                  :key="tag.id"
                >{{ tag.name }}</span>
              </div>
              <div class="artist-row__tag">
                <span
                  v-for="tag in col.value.filter((tag: Tag) => !tag.is_base)"
                  :key="tag.id"
                >{{ tag.name }}</span>
              </div>
            </div>
            <span v-else>{{ col.value }}</span>
          </q-td>
          <q-td class="q-gutter-x-sm" auto-width>
            <q-btn size="sm" @click="initArtistEdit(props.row)" label="Edit"/>
          </q-td>
        </q-tr>
      </template>
    </q-table>

    <MusicArtistsUpdateDialog
      v-if="showUpdateDialog"
      v-model="showUpdateDialog"
      :artist="artistForEdit"
      @updated="refreshArtists"
    />
  </div>
</template>
<script lang="ts" setup>
import { onMounted, provide, ref } from 'vue'
import { getIncluded, handleApiError } from 'src/utils/jsonapi'
import { api } from 'src/boot/axios'
import MusicArtistsUpdateDialog from 'src/components/admin/Music/MusicArtistsUpdateDialog.vue'

interface Tag {
  id: string
  name: string
  is_base: boolean
}

interface Artist {
  id: string
  name: string
  image: string
  description: string | null
  created_at: string
  relationships: {
    tags: {
      data: Tag[]
    }
  }
}

interface RelationshipItem {
  type: string
  id: string
  meta?: Record<string, any>
}

interface ResponseArtist {
  type: string
  id: string
  attributes: {
    name: string
    image: string
    description: string | null
    created_at: string
  }
  relationships: {
    tags: {
      data: RelationshipItem
    }
  }
}

interface IncludedItem {
  type: string
  id: string
  attributes: Record<string, any>
  relationships?: any
}

interface GetArtistApiResponse {
  data: ResponseArtist[]
  meta: {
    artists_count: number
  }
  included: IncludedItem[]
}

const columns = ref([{
  name: 'id',
  required: true,
  label: 'ID',
  align: 'left' as const,
  field: (row: Artist) => row.id,
  sortable: true,
  style: 'width: 40px'
}, {
  name: 'image',
  required: true,
  label: 'Image',
  align: 'center' as const,
  field: (row: Artist) => row.image,
  sortable: false,
  style: 'width: 60px'
}, {
  name: 'name',
  required: true,
  label: 'Name',
  align: 'left' as const,
  field: (row: Artist) => row.name,
  sortable: true
}, {
  name: 'tags',
  required: true,
  label: 'Tags',
  align: 'center' as const,
  field: (row: Artist) => row.relationships.tags.data,
  sortable: false
}, {
  name: 'createdAt',
  required: true,
  label: 'Created at',
  align: 'left' as const,
  field: (row: Artist) => row.created_at,
  sortable: true
}])
const total = ref(0)
const search = ref('')
const artists = ref<Artist[]>([])
const showUpdateDialog = ref(false)
const artistForEdit = ref<Artist>({
  id: '',
  name: '',
  image: '',
  description: null,
  created_at: '',
  relationships: {
    tags: {
      data: []
    }
  }
})

const getArtists = async (searchText: string = '', page: number = 0) => {
  let query = '?'
  if (searchText) {
    query += '&search=' + searchText
  }
  if (page) {
    query += '&page=' + page
  }
  await api.get<GetArtistApiResponse>('v1/music/artists' + query)
    .then(response => {
      artists.value = response.data.data.map(responseArtist => {
        return transformArtistFromResponse(responseArtist, response.data)
      }) as Artist[]
      total.value = response.data.meta.artists_count
    }).catch(error => {
      handleApiError(error)
    })
}

const transformArtistFromResponse = (responseArtist: ResponseArtist, responseData: any): Artist => {
  return {
    id: responseArtist.id,
    name: responseArtist.attributes.name,
    image: responseArtist.attributes.image,
    description: responseArtist.attributes.description,
    created_at: responseArtist.attributes.created_at,
    relationships: {
      tags: getIncluded('tags', responseArtist.relationships, responseData.included) as {data: Tag[]}
    }
  }
}

const initArtistEdit = (artist: Artist) => {
  artistForEdit.value = artist
  showUpdateDialog.value = true
}

const refreshArtists = (artist: Artist) => {
  for (const key in artists.value) {
    if (artists.value[key].id === artist.id) {
      artists.value[key] = artist
    }
  }
}

provide('transformArtistFromResponse', transformArtistFromResponse)

onMounted(() => {
  getArtists()
})
</script>
<style lang="scss" scoped>
.artist {
  &-edit {
    &__image {
      width: 250px;
      height: 250px;

      img {
        width: 100%;
        height: 100%;
      }
    }
  }

  &-row {
    &__image {
      width: 50px;
      height: 50px;
      overflow: hidden;

      img {
        width: 100%;
        object-fit: cover;
      }
    }

    &__tag {
      & span:not(:last-child) {
        &::after {
          content: ', '
        }
      }
    }
  }

  &-search {
    max-width: 400px;
  }
}
</style>
