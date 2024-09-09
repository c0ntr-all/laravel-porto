<template>
  <ArtistPageSkeleton v-if="loading"/>
  <template v-else>
    <div class="q-mb-sm">
      <q-btn
        icon="arrow_back"
        color="primary"
        :to="'/music'"
      >
        <div class="q-ml-xs">Вернуться назад</div>
      </q-btn>
    </div>

    <div class="artist-head q-mb-lg">
      <div class="artist-head__left">
        <div class="artist-head__image">
          <a :href="artist?.image"><img :src="artist?.image" alt=""></a>
        </div>
      </div>
      <div class="artist-head__right">
        <h2 class="artist-head__name">{{ artist?.name }}</h2>
        <div class="artist-head__description">
          <p v-if="artist?.description">{{ artist?.description }}</p>
          <p v-else class="text-grey-5">Описание отсутствует</p>
        </div>
        <div class="artist-head__tags">
          <div class="tags-list q-gutter-sm">
            <q-chip
              v-for="tag in artist?.relationships.tags.data"
              :key="tag.id"
              color="primary"
              text-color="white"
            >{{ tag.name }}</q-chip>
          </div>
        </div>
      </div>
    </div>

    <q-card class="q-mb-md" flat>
      <q-tabs
        v-model="tab"
        align="left"
        no-caps
        outside-arrows
        mobile-arrows
      >
        <q-route-tab
          name="tracks"
          label="Tracks"
          :to="{ name: 'artist-tracks', params: { id: artist?.id } }"
          exact
        />
        <q-route-tab
          name="albums"
          label="Albums"
          :to="{ name: 'artist-albums', params: { id: artist?.id } }"
          exact
        />
        <q-route-tab
          name="similar"
          label="Similar"
          :to="{ name: 'artist-similar', params: { id: artist?.id } }"
          exact
        />
      </q-tabs>
    </q-card>
    <q-tab-panels
      v-model="tab"
      animated
      swipeable
      vertical
      transition-prev="jump-up"
      transition-next="jump-up"
    >
      <q-tab-panel name="tracks" class="q-pa-none">
<!--        <TracksTab :artistId="props.id"/>-->
      </q-tab-panel>

      <q-tab-panel name="albums" class="q-pa-none">
        <ArtistTabAlbums :artistId="props.id"/>
      </q-tab-panel>

      <q-tab-panel name="similar" class="q-pa-none">
<!--        <SimilarTab/>-->
      </q-tab-panel>
    </q-tab-panels>
  </template>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'

import { api } from 'src/boot/axios'

// import TracksTab from 'components/client/music/artist/tabs/TracksTab.vue'
// import SimilarTab from 'components/client/music/artist/tabs/SimilarTab.vue'
import ArtistTabAlbums from 'src/components/client/Music/MusicArtistTabAlbums.vue'
import ArtistPageSkeleton from 'src/pages/client/Music/ArtistPageSkeleton.vue'
import { getIncluded } from 'src/utils/jsonapi'

interface Tag {
  id: string
  name: string
}

interface Artist {
  id: string
  name: string
  image: string
  description: string | null
  relationships: {
    tags: {
      data: Tag[]
    }
  }
}

interface IncludedItem {
  type: string
  id: string
  attributes: object
}

interface GetArtistApiResponse {
  data: {
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
        data: []
      }
    }
  },
  included: IncludedItem[]
}

const props = defineProps<{
  id: string
}>()

const $q = useQuasar()

const tab = ref('albums')
const artist = ref<Artist | null>(null)
const loading = ref(true)

const getArtist = async (id: string): Promise<void> => {
  await api.get<GetArtistApiResponse>(`v1/music/artists/${id}`)
    .then(response => {
      const responseArtist = response.data.data
      artist.value = {
        id: responseArtist.id,
        ...responseArtist.attributes,
        relationships: {
          tags: getIncluded('tags', responseArtist.relationships, response.data.included || [])
        }
      }
      loading.value = false
    }).catch(error => {
      $q.notify({
        type: 'negative',
        message: error.response.data.message || 'Error!'
      })
    })
}

onMounted(() => {
  getArtist(props.id)
})
</script>

<style lang="scss" scoped>
.artist-head {
  display: flex;
  column-gap: 1rem;
  padding: 1rem 0 0 0;

  &__image {
    img {
      width: 200px;
      height: 200px;
    }
  }

  &__name {
    margin: 0 0 1rem 0;
    font-size: 45px;
    line-height: 45px;
    font-weight: 700;
  }

  &__description {
    margin: 0 0 1rem 0;
  }
}
</style>
