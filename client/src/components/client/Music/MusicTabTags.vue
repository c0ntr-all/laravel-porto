<template>
  <div class="tags-tab">
    <div class="text-h6 q-mb-xs">Tags</div>
    <div class="text-caption text-grey-7 q-mb-md">
      Browse tag groups and open a tag to read its description
    </div>

    <q-card v-if="tagStore.isGroupsLoading || tagStore.isTagsLoading" flat>
      <q-card-section>
        <q-skeleton type="text" width="30%" />
        <q-skeleton type="QChip" v-for="n in 6" :key="n" class="q-mr-sm q-mt-sm" />
      </q-card-section>
    </q-card>

    <div v-else class="q-gutter-md">
      <q-card
        v-for="group in visibleGroups"
        :key="group.id"
        flat
        bordered
      >
        <q-card-section>
          <div class="text-subtitle1 text-weight-medium">{{ group.name }}</div>
          <div v-if="group.description" class="text-caption text-grey-7 q-mt-xs">
            {{ group.description }}
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <MusicTagTree
            v-if="tagStore.tagsByGroup(group.id).length"
            :tags="tagStore.tagsByGroup(group.id)"
            @select="openTag"
          />
          <div v-else class="text-caption text-grey-6">No tags in this group</div>
        </q-card-section>
      </q-card>

      <q-card v-if="tagStore.ungroupedTags.length" flat bordered>
        <q-card-section>
          <div class="text-subtitle1 text-weight-medium">Other</div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <MusicTagTree
            :tags="tagStore.ungroupedTags"
            @select="openTag"
          />
        </q-card-section>
      </q-card>

      <AppNoResultsPlug
        v-if="!visibleGroups.length && !tagStore.ungroupedTags.length"
        title="No tags yet"
        body="Create tag groups in the music manager"
      />
    </div>

    <q-dialog v-model="showDialog">
      <q-card class="tag-dialog" v-if="selectedTag">
        <q-card-section class="flex justify-between items-start">
          <div>
            <div class="text-h6">{{ selectedTag.name }}</div>
            <div v-if="selectedTag.slug" class="text-caption text-grey-6">
              {{ selectedTag.slug }}
            </div>
          </div>
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <p v-if="selectedTag.description" class="q-mb-none">
            {{ selectedTag.description }}
          </p>
          <p v-else class="text-grey-6 q-mb-none">No description</p>
        </q-card-section>
        <q-separator v-if="selectedTag.slug" />
        <q-card-section v-if="selectedTag.slug" class="flex justify-end">
          <q-btn
            :to="`/music/tags/${selectedTag.slug}`"
            color="primary"
            unelevated
            no-caps
            label="Open tag page"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import MusicTagTree from 'src/components/client/Music/MusicTagTree.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { IMusicTag } from 'src/types'

const tagStore = useMusicTagStore()
const showDialog = ref(false)
const selectedTag = ref<IMusicTag | null>(null)

const visibleGroups = computed(() => tagStore.groups.filter(group => group.is_active))

const openTag = (tag: IMusicTag) => {
  selectedTag.value = tag
  showDialog.value = true
}

onMounted(() => {
  if (!tagStore.groups.length || !tagStore.tags.length) {
    void tagStore.loadAll()
  }
})
</script>

<style lang="scss" scoped>
.tag-dialog {
  min-width: 320px;
  max-width: 520px;
}
</style>
