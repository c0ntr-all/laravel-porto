<template>
  <div>
    <div class="row items-center q-col-gutter-sm q-mb-md">
      <div class="col">
        <div class="text-h6">Artists</div>
        <div class="text-caption text-grey-7">{{ admin.artists.length }} loaded</div>
      </div>
      <div class="col-12 col-sm-5 col-md-4">
        <q-input
          v-model="search"
          label="Search artists"
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
    </div>

    <q-card flat bordered>
      <q-inner-loading :showing="admin.isArtistsLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="admin.artists.length" separator>
        <q-item
          v-for="artist in admin.artists"
          :key="artist.id"
          clickable
          class="artist-row"
          @click="openEdit(artist)"
        >
          <q-item-section avatar>
            <q-avatar size="56px" rounded>
              <img v-if="artist.image" :src="artist.image" :alt="artist.name">
              <q-icon v-else name="person" />
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-subtitle1 text-weight-medium">{{ artist.name }}</q-item-label>
            <q-item-label caption>
              {{ artist.description || 'No description' }}
            </q-item-label>
            <div v-if="artist.tags.length" class="q-gutter-xs q-mt-xs">
              <q-chip
                v-for="tag in artist.tags.slice(0, 6)"
                :key="tag.id"
                size="sm"
                outline
                color="primary"
                dense
              >
                {{ tag.name }}
              </q-chip>
              <q-chip
                v-if="artist.tags.length > 6"
                size="sm"
                dense
              >
                +{{ artist.tags.length - 6 }}
              </q-chip>
            </div>
          </q-item-section>
          <q-item-section side>
            <q-btn
              icon="edit"
              color="primary"
              flat
              round
              dense
              @click.stop="openEdit(artist)"
            >
              <q-tooltip>Edit artist</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="!admin.isArtistsLoading" class="q-pa-lg text-grey-6">
        No artists found
      </div>

      <div
        v-if="admin.hasMoreArtists"
        ref="sentinel"
        class="list-sentinel"
      />
      <div v-if="admin.isArtistsLoadingMore" class="flex justify-center q-py-md">
        <q-spinner color="primary" />
      </div>
    </q-card>

    <MusicArtistsUpdateDialog
      v-if="editing"
      v-model="showDialog"
      :artist="editing"
      @saved="onSaved"
    />
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicAdminStore } from 'src/stores/modules/musicAdminStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import MusicArtistsUpdateDialog from 'src/components/admin/Music/MusicArtistsUpdateDialog.vue'
import { IArtist } from 'src/types'

const admin = useMusicAdminStore()
const search = ref(admin.artistSearch)
const showDialog = ref(false)
const editing = ref<IArtist | null>(null)

const onSearch = (value: string | number | null) => {
  void admin.getArtists({ name: String(value ?? '') })
}

const openEdit = (artist: IArtist) => {
  editing.value = artist
  showDialog.value = true
}

const onSaved = (artist: IArtist) => {
  editing.value = artist
  showDialog.value = false
}

const { sentinel } = useScrollSentinel(
  () => { void admin.getArtists({ append: true }) },
  () => admin.hasMoreArtists && !admin.isArtistsLoading && !admin.isArtistsLoadingMore
)

onMounted(() => {
  void admin.getArtists()
})
</script>

<style lang="scss" scoped>
.artist-row {
  min-height: 84px;
}

.list-sentinel {
  height: 1px;
}
</style>
