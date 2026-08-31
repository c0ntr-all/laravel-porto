<template>
  <div class="playlists-tab">
    <div class="row items-center q-col-gutter-sm q-mb-md">
      <div class="col">
        <div class="text-h6">Playlists</div>
        <div class="text-caption text-grey-7">
          {{ playlistStore.playlists.length }} collection{{ playlistStore.playlists.length === 1 ? '' : 's' }}
        </div>
      </div>
      <div class="col-12 col-sm">
        <q-input
          v-model="searchText"
          label="Search playlists"
          outlined
          dense
          debounce="400"
          clearable
          @update:model-value="onSearch"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-auto">
        <q-btn
          icon="add"
          label="Create playlist"
          color="primary"
          no-caps
          unelevated
          @click="openCreate"
        />
      </div>
    </div>

    <q-dialog v-model="createPlaylistModal">
      <q-card class="playlist-modal">
        <q-card-section class="flex justify-between items-center">
          <div class="text-h6">Create playlist</div>
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="q-gutter-md">
          <q-input
            v-model="newPlaylistName"
            type="text"
            label="Name"
            filled
            dense
            autofocus
          />
          <q-input
            v-model="newPlaylistDescription"
            type="textarea"
            label="Description"
            filled
            dense
            autogrow
          />
        </q-card-section>
        <q-separator />
        <q-card-section class="flex justify-end q-gutter-sm">
          <q-btn flat no-caps v-close-popup>Cancel</q-btn>
          <q-btn
            color="primary"
            unelevated
            no-caps
            :disable="!newPlaylistName.trim()"
            :loading="playlistStore.isPlaylistSaving"
            @click="createPlaylist"
          >
            Create
          </q-btn>
        </q-card-section>
      </q-card>
    </q-dialog>

    <div v-if="playlistStore.isPlaylistsLoading" class="row q-col-gutter-md">
      <div
        v-for="n in 8"
        :key="n"
        class="col-6 col-sm-4 col-md-3"
      >
        <MusicPlaylistCardSkeleton />
      </div>
    </div>

    <template v-else-if="playlistStore.playlists.length">
      <div class="row q-col-gutter-md">
        <div
          v-for="playlist in playlistStore.playlists"
          :key="playlist.id"
          class="col-6 col-sm-4 col-md-3"
        >
          <MusicPlaylistCard :playlist="playlist" />
        </div>
      </div>
      <div
        v-if="playlistStore.hasMorePlaylists"
        ref="sentinel"
        class="playlists-list-sentinel"
      />
      <div
        v-if="playlistStore.isPlaylistsLoadingMore"
        class="flex justify-center q-my-md"
      >
        <q-spinner color="primary" size="2em" />
      </div>
    </template>

    <AppNoResultsPlug
      v-else
      title="There are no playlists yet"
      body="Create one to start collecting tracks"
    />
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicPlaylistStore } from 'src/stores/modules/musicPlaylistStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import MusicPlaylistCard from 'src/components/client/Music/MusicPlaylistCard.vue'
import MusicPlaylistCardSkeleton from 'src/components/client/Music/MusicPlaylistCardSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const playlistStore = useMusicPlaylistStore()
const createPlaylistModal = ref(false)
const newPlaylistName = ref('')
const newPlaylistDescription = ref('')
const searchText = ref(playlistStore.playlistListName)

const onSearch = (value: string | number | null) => {
  void playlistStore.getPlaylists({ name: String(value ?? '') })
}

const openCreate = () => {
  newPlaylistName.value = ''
  newPlaylistDescription.value = ''
  createPlaylistModal.value = true
}

const createPlaylist = async () => {
  const created = await playlistStore.createPlaylist({
    name: newPlaylistName.value.trim(),
    description: newPlaylistDescription.value.trim() || null
  })

  if (created) {
    createPlaylistModal.value = false
  }
}

const { sentinel } = useScrollSentinel(
  () => { void playlistStore.getPlaylists({ append: true }) },
  () => playlistStore.hasMorePlaylists && !playlistStore.isPlaylistsLoading && !playlistStore.isPlaylistsLoadingMore
)

onMounted(() => {
  if (!playlistStore.playlists.length) {
    void playlistStore.getPlaylists()
  }
})
</script>

<style lang="scss" scoped>
.playlist-modal {
  min-width: 360px;
  max-width: 480px;
}

.playlists-list-sentinel {
  width: 100%;
  height: 1px;
}
</style>
