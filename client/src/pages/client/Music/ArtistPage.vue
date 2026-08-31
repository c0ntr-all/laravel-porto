<template>
  <ArtistPageSkeleton v-if="catalog.isArtistLoading"/>
  <template v-else-if="artist">
    <div class="q-mb-sm">
      <q-btn
        icon="arrow_back"
        color="primary"
        :to="{ name: 'music-artists' }"
      >
        <div class="q-ml-xs">Вернуться назад</div>
      </q-btn>
    </div>

    <div class="artist-head q-mb-lg">
      <div class="artist-head__left">
        <div class="artist-head__image">
          <a :href="artist.image"><img :src="artist.image" :alt="artist.name"></a>
        </div>
      </div>
      <div class="artist-head__right">
        <h2 class="artist-head__name">{{ artist.name }}</h2>
        <div class="artist-head__description">
          <p v-if="artist.description">{{ artist.description }}</p>
          <p v-else class="text-grey-5">Описание отсутствует</p>
        </div>
        <div v-if="artist.tags.length" class="artist-head__tags">
          <div class="tags-list q-gutter-sm">
            <q-chip
              v-for="tag in artist.tags"
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
          :to="{ name: 'artist-tracks', params: { id: artist.id } }"
          exact
        />
        <q-route-tab
          name="albums"
          label="Albums"
          :to="{ name: 'artist-albums', params: { id: artist.id } }"
          exact
        />
        <q-route-tab
          name="similar"
          label="Similar"
          :to="{ name: 'artist-similar', params: { id: artist.id } }"
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
        <MusicArtistTabTracks
          v-if="tab === 'tracks'"
          :artist-id="artist.id"
          :artist-name="artist.name"
        />
      </q-tab-panel>

      <q-tab-panel name="albums" class="q-pa-none">
        <ArtistTabAlbums :artistId="artist.id"/>
      </q-tab-panel>

      <q-tab-panel name="similar" class="q-pa-none">
      </q-tab-panel>
    </q-tab-panels>
  </template>
  <AppNoResultsPlug
    v-else
    title="Artist not found"
    body="Try another artist"
  />
</template>

<script lang="ts" setup>
import { computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import ArtistTabAlbums from 'src/components/client/Music/MusicArtistTabAlbums.vue'
import MusicArtistTabTracks from 'src/components/client/Music/MusicArtistTabTracks.vue'
import ArtistPageSkeleton from 'src/pages/client/Music/ArtistPageSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  id: string
}>()

const route = useRoute()
const catalog = useMusicCatalogStore()
const { artist } = storeToRefs(catalog)

const tab = computed({
  get: () => {
    if (route.name === 'artist-tracks') {
      return 'tracks'
    }

    if (route.name === 'artist-similar') {
      return 'similar'
    }

    return 'albums'
  },
  set: () => undefined
})

watch(() => props.id, (id) => {
  catalog.getArtist(id)
}, { immediate: true })
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
