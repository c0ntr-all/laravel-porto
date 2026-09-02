<template>
  <div>
    <div class="row items-end q-col-gutter-sm q-mb-md">
      <div class="col-12 col-md">
        <div class="text-h6">Tracks</div>
        <div class="text-caption text-grey-7">{{ admin.tracks.length }} loaded</div>
      </div>
      <div class="col-12 col-sm-4 col-md-3">
        <q-input
          v-model="artistSearch"
          label="Artist"
          outlined
          dense
          debounce="400"
          clearable
          @update:model-value="onArtistSearch"
        >
          <template #prepend>
            <q-icon name="person" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-sm-4 col-md-3">
        <q-input
          v-model="albumSearch"
          label="Album"
          outlined
          dense
          debounce="400"
          clearable
          @update:model-value="onAlbumSearch"
        >
          <template #prepend>
            <q-icon name="album" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-sm-4 col-md-3">
        <q-input
          v-model="trackSearch"
          label="Track"
          outlined
          dense
          debounce="400"
          clearable
          @update:model-value="onTrackSearch"
        >
          <template #prepend>
            <q-icon name="music_note" />
          </template>
        </q-input>
      </div>
    </div>

    <q-card flat bordered>
      <q-inner-loading :showing="admin.isTracksLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="admin.tracks.length" separator>
        <q-item
          v-for="track in admin.tracks"
          :key="track.id"
          clickable
          @click="playTrack(track)"
        >
          <q-item-section avatar>
            <q-avatar size="48px" rounded>
              <img v-if="track.image || track.album?.image" :src="track.image || track.album?.image" :alt="track.name">
              <q-icon v-else name="music_note" />
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-weight-medium">
              <span v-if="track.number" class="text-grey-6 q-mr-xs">{{ track.number }}.</span>
              <MusicTrackName :name="track.name" :credits="track.credits" />
            </q-item-label>
            <q-item-label caption>
              {{ track.artist || 'Unknown artist' }}
              <span v-if="track.album"> · {{ track.album.name }}</span>
            </q-item-label>
            <MusicAlbumMetaChips
              v-if="track.album"
              class="q-mt-xs"
              :album-type="track.album.album_type"
              :edition="track.album.edition"
            />
            <div v-if="track.tags?.length" class="q-gutter-xs q-mt-xs">
              <q-chip
                v-for="tag in track.tags.slice(0, 4)"
                :key="tag.id"
                size="sm"
                outline
                dense
              >
                {{ tag.name }}
              </q-chip>
            </div>
          </q-item-section>
          <q-item-section side>
            <div class="text-caption text-grey-7">{{ track.duration }}</div>
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="!admin.isTracksLoading" class="q-pa-lg text-grey-6">
        No tracks found
      </div>

      <div v-if="admin.hasMoreTracks" ref="sentinel" class="list-sentinel" />
      <div v-if="admin.isTracksLoadingMore" class="flex justify-center q-py-md">
        <q-spinner color="primary" />
      </div>
    </q-card>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicAdminStore } from 'src/stores/modules/musicAdminStore'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { ITrack } from 'src/types'
import MusicAlbumMetaChips from 'src/components/client/Music/MusicAlbumMetaChips.vue'
import MusicTrackName from 'src/components/client/Music/MusicTrackName.vue'

const admin = useMusicAdminStore()
const musicPlayer = useMusicPlayer()
const artistSearch = ref(admin.trackArtistSearch)
const albumSearch = ref(admin.trackAlbumSearch)
const trackSearch = ref(admin.trackNameSearch)

const onArtistSearch = (value: string | number | null) => {
  void admin.getTracks({ artist: String(value ?? '') })
}

const onAlbumSearch = (value: string | number | null) => {
  void admin.getTracks({ album: String(value ?? '') })
}

const onTrackSearch = (value: string | number | null) => {
  void admin.getTracks({ name: String(value ?? '') })
}

const playTrack = (track: ITrack) => {
  musicPlayer.toggleTrack(track, admin.tracks)
}

const { sentinel } = useScrollSentinel(
  () => { void admin.getTracks({ append: true }) },
  () => admin.hasMoreTracks && !admin.isTracksLoading && !admin.isTracksLoadingMore
)

onMounted(() => {
  void admin.getTracks()
})
</script>

<style lang="scss" scoped>
.list-sentinel {
  height: 1px;
}
</style>
