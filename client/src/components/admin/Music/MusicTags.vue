<template>
  <div class="q-mb-lg">
    <div class="row items-center q-mb-md">
      <div class="text-h6">Tag groups</div>
      <q-space/>
      <q-btn
        icon="add"
        label="Create group"
        color="primary"
        dense
        @click="openGroupForm()"
      />
    </div>
    <MusicTagGroupsTable @edit="openGroupForm"/>
  </div>

  <div>
    <div class="row items-center q-mb-md">
      <div class="text-h6">Tags</div>
      <q-space/>
      <q-btn
        icon="add"
        label="Create tag"
        color="primary"
        dense
        :disable="!store.groups.length"
        @click="openTagForm()"
      >
        <q-tooltip v-if="!store.groups.length">Create a tag group first</q-tooltip>
      </q-btn>
    </div>

    <q-input
      class="q-mb-md"
      v-model="filter"
      label="Search tags"
      dense
      filled
    >
      <template v-slot:append>
        <q-icon
          v-if="filter"
          name="clear"
          class="cursor-pointer"
          @click="filter = ''"
        />
      </template>
    </q-input>

    <div v-if="store.isTagsLoading">Loading tags…</div>

    <template v-else>
      <div
        v-for="group in store.groups"
        :key="group.id"
        class="q-mb-md"
      >
        <div class="text-subtitle1 q-mb-xs">{{ group.name }}</div>
        <MusicTagsList
          v-if="store.tagsByGroup(group.id).length"
          :nodes="store.tagsByGroup(group.id)"
          :filter="filter"
          @create-child="openTagForm(undefined, $event)"
          @edit="openTagForm"
          @delete="openTagDelete"
        />
        <div v-else class="text-grey-7 q-mb-sm">No tags in this group yet</div>
      </div>

      <div v-if="store.ungroupedTags.length" class="q-mb-md">
        <div class="text-subtitle1 q-mb-xs">Ungrouped</div>
        <MusicTagsList
          :nodes="store.ungroupedTags"
          :filter="filter"
          @create-child="openTagForm(undefined, $event)"
          @edit="openTagForm"
          @delete="openTagDelete"
        />
      </div>
    </template>
  </div>

  <MusicTagGroupFormDialog
    v-model="showGroupForm"
    :group="editingGroup"
  />

  <MusicTagFormDialog
    v-model="showTagForm"
    :tag="editingTag"
    :parent="parentTag"
  />

  <MusicTagsDeleteDialog
    v-model="showTagDelete"
    :tag="deletingTag"
  />
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import MusicTagGroupsTable from 'src/components/admin/Music/MusicTagGroupsTable.vue'
import MusicTagGroupFormDialog from 'src/components/admin/Music/MusicTagGroupFormDialog.vue'
import MusicTagFormDialog from 'src/components/admin/Music/MusicTagFormDialog.vue'
import MusicTagsList from 'src/components/admin/Music/MusicTagsList.vue'
import MusicTagsDeleteDialog from 'src/components/admin/Music/MusicTagsDeleteDialog.vue'
import { IMusicTag, IMusicTagGroup } from 'src/types'

const store = useMusicTagStore()
const filter = ref('')
const showGroupForm = ref(false)
const showTagForm = ref(false)
const showTagDelete = ref(false)
const editingGroup = ref<IMusicTagGroup | null>(null)
const editingTag = ref<IMusicTag | null>(null)
const parentTag = ref<IMusicTag | null>(null)
const deletingTag = ref<IMusicTag | null>(null)

const openGroupForm = (group?: IMusicTagGroup) => {
  editingGroup.value = group ?? null
  showGroupForm.value = true
}

const openTagForm = (tag?: IMusicTag, parent?: IMusicTag) => {
  editingTag.value = tag ?? null
  parentTag.value = parent ?? null
  showTagForm.value = true
}

const openTagDelete = (tag: IMusicTag) => {
  deletingTag.value = tag
  showTagDelete.value = true
}

onMounted(() => {
  void store.loadAll()
})
</script>
