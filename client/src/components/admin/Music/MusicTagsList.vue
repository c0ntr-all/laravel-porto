<template>
  <q-tree
    :nodes="nodes"
    node-key="name"
    :filter="filter"
    :filter-method="filterMethod"
    children-key="tags"
  >
    <template v-slot:default-header="scope">
      <div class="flex items-center">
        <div>{{ scope.node.name }}</div>
        <div class="flex q-ml-xs">
          <q-btn
            @click.stop="openCreateChildDialog(scope.node)"
            icon="add"
            size="xs"
            dense
            flat
            rounded
          />
          <q-btn
            @click.stop="openUpdateDialog(scope.node)"
            icon="edit"
            size="xs"
            color="primary"
            dense
            flat
            rounded
          />
          <q-btn
            @click.stop="openDeleteDialog(scope.node)"
            icon="delete"
            size="xs"
            color="primary"
            dense
            flat
            rounded
          />
        </div>
      </div>
    </template>
  </q-tree>

  <MusicTagsCreateChildDialog
    v-if="showCreateChildDialog"
    v-model="showCreateChildDialog"
    :tag="tagProp as TagPropCreateChild"
  />

  <MusicTagsUpdateDialog
    v-if="showUpdateDialog"
    v-model="showUpdateDialog"
    :tag="tagProp as TagPropBase"
  />

  <MusicTagsDeleteDialog
    v-if="showDeleteDialog"
    v-model="showDeleteDialog"
    :tag="tagProp as TagPropBase"
  />
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import MusicTagsCreateChildDialog from 'src/components/admin/Music/MusicTagsCreateChildTagDialog.vue'
import MusicTagsUpdateDialog from 'src/components/admin/Music/MusicTagsUpdateDialog.vue'
import MusicTagsDeleteDialog from 'src/components/admin/Music/MusicTagsDeleteDialog.vue'

interface Tag {
  id: string
  name: string
  content: string | null
  is_base: boolean
  parent_id: number | null
  tags: Tag[] | []
}

interface TagPropBase {
  id: string
  name: string
  content: string | null
  is_base: boolean
  parentTag?: TagPropBase
}

interface TagPropCreateChild extends TagPropBase{
  parentTag: TagPropCreateChild
}

type TagProp = TagPropBase | TagPropCreateChild

const props = defineProps<{
  nodes: any[]
}>()
const nodes = ref<any[]>(props.nodes)
const filter = ref('')
const tagProp = ref<TagProp>({
  id: '',
  name: '',
  content: null,
  is_base: true
})
const showCreateChildDialog = ref(false)
const showUpdateDialog = ref(false)
const showDeleteDialog = ref(false)

const openCreateChildDialog = (tag: Tag) => {
  showCreateChildDialog.value = true
  tagProp.value.parentTag = tag
}

const openUpdateDialog = (tag: Tag) => {
  showUpdateDialog.value = true
  tagProp.value = tag
}

const openDeleteDialog = (tag: Tag) => {
  showDeleteDialog.value = true
  tagProp.value = tag
}

const filterMethod = (node: Tag, text: string) => {
  return node.name.toLowerCase().includes(text.toLowerCase())
}
</script>
