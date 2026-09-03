<template>
  <div>
    <div class="row items-end q-col-gutter-sm q-mb-md">
      <div class="col-12 col-md">
        <div class="text-h6">Albums</div>
        <div class="text-caption text-grey-7">
          Main releases only. Versions are grouped under the original album.
        </div>
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
    </div>

    <q-card flat bordered>
      <q-inner-loading :showing="admin.isAlbumsLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="admin.albums.length" separator>
        <template v-for="album in admin.albums" :key="album.id">
          <q-item class="album-row">
            <q-item-section avatar>
              <q-avatar size="56px" rounded>
                <img v-if="album.image" :src="album.image" :alt="album.name">
                <q-icon v-else name="album" />
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-subtitle1 text-weight-medium">
                {{ album.name }}
              </q-item-label>
              <q-item-label caption>
                {{ artistNames(album) }}
                <span v-if="albumYear(album.date)"> · {{ albumYear(album.date) }}</span>
              </q-item-label>
              <MusicAlbumMetaChips
                class="q-mt-xs"
                :album-type="album.album_type"
                :edition="album.edition"
              />
              <div v-if="album.tags.length" class="q-gutter-xs q-mt-xs">
                <q-chip
                  v-for="tag in album.tags.slice(0, 4)"
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
              <div class="row no-wrap q-gutter-xs items-center">
                <q-btn
                  v-if="album.versions.length"
                  :icon="expanded[album.id] ? 'expand_less' : 'expand_more'"
                  flat
                  round
                  dense
                  @click="toggleExpanded(album.id)"
                >
                  <q-tooltip>{{ album.versions.length }} versions</q-tooltip>
                </q-btn>
                <q-badge v-if="album.versions.length" color="grey-7">
                  {{ album.versions.length }}
                </q-badge>
                <q-btn icon="edit" color="primary" flat round dense @click="openEdit(album)">
                  <q-tooltip>Edit</q-tooltip>
                </q-btn>
                <q-btn icon="delete" color="negative" flat round dense @click="confirmDelete(album)">
                  <q-tooltip>Delete</q-tooltip>
                </q-btn>
              </div>
            </q-item-section>
          </q-item>

          <q-slide-transition>
            <div v-if="expanded[album.id] && album.versions.length" class="album-versions">
              <q-item
                v-for="version in album.versions"
                :key="version.id"
                dense
                class="album-version"
              >
                <q-item-section avatar>
                  <q-avatar size="36px" rounded>
                    <img v-if="version.image" :src="version.image" :alt="version.name">
                    <q-icon v-else name="album" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ version.name }}</q-item-label>
                  <MusicAlbumMetaChips
                    class="q-mt-xs"
                    :album-type="version.album_type"
                    :edition="version.edition"
                  />
                  <q-item-label caption>{{ albumYear(version.date) }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-btn icon="edit" flat round dense size="sm" @click="openEditById(version.id)" />
                  <q-btn icon="delete" color="negative" flat round dense size="sm" @click="confirmDelete(version)" />
                </q-item-section>
              </q-item>
            </div>
          </q-slide-transition>
        </template>
      </q-list>

      <div v-else-if="!admin.isAlbumsLoading" class="q-pa-lg text-grey-6">
        No albums found
      </div>

      <div v-if="admin.hasMoreAlbums" ref="sentinel" class="list-sentinel" />
      <div v-if="admin.isAlbumsLoadingMore" class="flex justify-center q-py-md">
        <q-spinner color="primary" />
      </div>
    </q-card>

    <MusicAlbumFormDialog
      v-if="editing"
      v-model="showDialog"
      :album="editing"
      @saved="onSaved"
    />

    <q-dialog v-model="showDelete">
      <q-card style="min-width: 320px">
        <q-card-section class="text-h6">Delete album</q-card-section>
        <q-card-section>
          Delete “{{ deleting?.name }}”? Versions of this album will become standalone releases.
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat v-close-popup>Cancel</q-btn>
          <q-btn
            color="negative"
            unelevated
            :loading="admin.isAlbumSaving"
            @click="runDelete"
          >
            Delete
          </q-btn>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, reactive, ref } from 'vue'
import { useMusicAdminStore } from 'src/stores/modules/musicAdminStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import MusicAlbumFormDialog from 'src/components/admin/Music/MusicAlbumFormDialog.vue'
import MusicAlbumMetaChips from 'src/components/client/Music/MusicAlbumMetaChips.vue'
import { albumYear } from 'src/utils/albumDate'
import { IAlbum, IAlbumVersion } from 'src/types'

const admin = useMusicAdminStore()
const artistSearch = ref(admin.albumArtistSearch)
const albumSearch = ref(admin.albumNameSearch)
const expanded = reactive<Record<string, boolean>>({})
const showDialog = ref(false)
const editing = ref<IAlbum | null>(null)
const showDelete = ref(false)
const deleting = ref<IAlbum | IAlbumVersion | null>(null)

const artistNames = (album: IAlbum) => (
  album.artists.map(artist => artist.name).join(' • ') || 'Unknown artist'
)

const onArtistSearch = (value: string | number | null) => {
  void admin.getAlbums({ artist: String(value ?? '') })
}

const onAlbumSearch = (value: string | number | null) => {
  void admin.getAlbums({ name: String(value ?? '') })
}

const toggleExpanded = (id: string) => {
  expanded[id] = !expanded[id]
}

const openEdit = (album: IAlbum) => {
  editing.value = album
  showDialog.value = true
}

const openEditById = async (id: string) => {
  const album = await admin.getAlbum(id)
  if (album) {
    editing.value = album
    showDialog.value = true
  }
}

const confirmDelete = (album: IAlbum | IAlbumVersion) => {
  deleting.value = album
  showDelete.value = true
}

const runDelete = async () => {
  if (!deleting.value) {
    return
  }

  const ok = await admin.deleteAlbum(deleting.value.id)
  if (ok) {
    showDelete.value = false
    deleting.value = null
  }
}

const onSaved = () => {
  showDialog.value = false
  editing.value = null
}

const { sentinel } = useScrollSentinel(
  () => { void admin.getAlbums({ append: true }) },
  () => admin.hasMoreAlbums && !admin.isAlbumsLoading && !admin.isAlbumsLoadingMore
)

onMounted(() => {
  void admin.getAlbums()
})
</script>

<style lang="scss" scoped>
.album-row {
  min-height: 84px;
}

.album-versions {
  background: #fafafa;
  padding-left: 24px;
}

.list-sentinel {
  height: 1px;
}
</style>
