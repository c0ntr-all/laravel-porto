<template>
  <div class="upload-session-details q-pa-md">
    <q-inner-loading :showing="loading" color="primary" />

    <q-banner
      v-if="upload.error_message"
      class="q-mb-md"
      dense
      rounded
      :class="upload.status === 'failed' ? 'bg-negative text-white' : 'bg-warning'"
    >
      {{ upload.error_message }}
    </q-banner>

    <div class="text-subtitle2 q-mb-sm">Artists</div>
    <div v-if="artists.length" class="q-gutter-xs q-mb-md">
      <template v-for="artist in artists" :key="artist.id || artist.name">
        <router-link
          v-if="artist.id"
          :to="`/music/artists/${artist.id}`"
          class="upload-session-details__link"
        >
          <q-chip color="primary" text-color="white" clickable dense>
            {{ artist.name }}
          </q-chip>
        </router-link>
        <q-chip
          v-else
          color="primary"
          text-color="white"
          dense
        >
          {{ artist.name }}
        </q-chip>
      </template>
    </div>
    <div v-else class="text-grey-6 q-mb-md">No artists in this session</div>

    <div class="row items-center q-mb-sm">
      <div class="text-subtitle2">Tracks</div>
      <q-space />
      <div class="text-caption text-grey-7">
        {{ upload.tracks_created }} created ·
        {{ upload.tracks_updated }} updated ·
        {{ upload.tracks_skipped }} skipped ·
        {{ upload.tracks_failed }} failed
      </div>
    </div>

    <q-scroll-area v-if="albumGroups.length" class="upload-session-details__tracks">
      <div
        v-for="group in albumGroups"
        :key="group.albumId || group.albumName"
        class="q-mb-md"
      >
        <div class="text-weight-medium q-mb-xs">
          <router-link
            v-if="group.albumId"
            :to="{ name: 'album', params: { id: group.albumId } }"
            class="upload-session-details__album-link"
          >
            {{ albumTitle(group) }}
          </router-link>
          <span v-else>{{ albumTitle(group) }}</span>
          <span class="text-caption text-grey-6 q-ml-xs">{{ group.tracks.length }}</span>
        </div>
        <q-markup-table flat dense>
          <tbody>
            <tr v-for="track in group.tracks" :key="track.id">
              <td class="text-left">{{ track.track_name || '—' }}</td>
              <td class="text-right" style="width: 110px">
                <q-badge :color="trackStatusColor(track.status)">
                  {{ track.status }}
                </q-badge>
              </td>
              <td class="text-grey-6" style="max-width: 280px">
                <span :title="track.source_path">{{ track.source_path }}</span>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </div>
    </q-scroll-area>
    <div v-else-if="!loading" class="text-grey-6">No track logs in this session</div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { groupUploadTracksByAlbum } from 'src/api/mappers/Music/upload.mapper'
import { IArtistShort, IMusicUpload, IMusicUploadAlbumGroup, MusicUploadTrackStatus } from 'src/types'

const props = defineProps<{
  upload: IMusicUpload
  loading?: boolean
}>()

const artists = computed<IArtistShort[]>(() => {
  if (props.upload.artists.length) {
    return props.upload.artists
  }

  if (!props.upload.artist_name) {
    return []
  }

  return [{
    id: props.upload.artist_id ?? '',
    name: props.upload.artist_name
  }]
})

const albumGroups = computed(() => groupUploadTracksByAlbum(props.upload.tracks, props.upload.albums))

const albumTitle = (group: IMusicUploadAlbumGroup): string => (
  group.albumYear ? `${group.albumYear} - ${group.albumName}` : group.albumName
)

const trackStatusColor = (status: MusicUploadTrackStatus): string => {
  const colors: Record<MusicUploadTrackStatus, string> = {
    created: 'positive',
    updated: 'info',
    skipped: 'grey',
    failed: 'negative'
  }

  return colors[status]
}
</script>

<style lang="scss" scoped>
.upload-session-details {
  position: relative;
  min-height: 80px;
  background: #fafbff;

  &__tracks {
    height: 320px;
  }

  &__link {
    text-decoration: none;
  }

  &__album-link {
    color: inherit;
    text-decoration: none;

    &:hover {
      color: var(--q-primary);
      text-decoration: underline;
    }
  }
}
</style>
