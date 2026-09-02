<template>
  <div class="music-filter">
    <div class="text-h6 q-mb-sm">Filters</div>

    <slot name="before-tags" />

    <q-btn-toggle
      v-model="tagsMatch"
      class="q-mb-sm full-width"
      no-caps
      unelevated
      spread
      toggle-color="primary"
      color="grey-2"
      text-color="primary"
      :options="[
        { label: 'Any tag', value: 'or' },
        { label: 'All tags', value: 'and' }
      ]"
    />

    <q-toggle
      v-model="tagsNested"
      class="q-mb-md"
      label="Include nested tags"
      color="primary"
      dense
    />

    <div v-if="selectedCount" class="q-mb-sm text-caption text-grey-7">
      {{ selectedCount }} selected
    </div>

    <q-inner-loading :showing="tagStore.isGroupsLoading || tagStore.isTagsLoading">
      <q-spinner size="2em" color="primary" />
    </q-inner-loading>

    <q-list v-if="groupTrees.length" separator>
      <q-expansion-item
        v-for="group in groupTrees"
        :key="group.id"
        :label="group.name"
        :caption="group.caption"
        default-opened
        dense
        header-class="text-weight-medium"
      >
        <q-tree
          v-if="group.nodes.length"
          class="q-mb-sm"
          :nodes="group.nodes"
          node-key="id"
          tick-strategy="strict"
          :ticked="tickedByGroup[group.id] ?? []"
          default-expand-all
          dense
          @update:ticked="ids => onGroupTicked(group.id, [...ids].map(String))"
        />
        <div v-else class="text-caption text-grey-6 q-pa-sm">No tags</div>
      </q-expansion-item>
    </q-list>

    <div class="q-mt-md">
      <q-btn
        color="grey"
        label="Reset"
        outline
        unelevated
        no-caps
        class="full-width"
        :disable="!selectedCount && tagsMatch === 'or' && tagsNested && !extraDirty"
        @click="resetFilter"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import { IMusicTag, IMusicTagGroup } from 'src/types'

interface TagTreeNode {
  id: string
  label: string
  children?: TagTreeNode[]
}

interface GroupTree {
  id: string
  name: string
  caption: string
  nodes: TagTreeNode[]
}

const emit = defineEmits<{
  change: [payload: { tags: string[]; tagsMatch: 'and' | 'or'; tagsNested: boolean }]
  reset: []
}>()

const props = withDefaults(defineProps<{
  tagsMatch?: 'and' | 'or'
  tagsNested?: boolean
  selectedTags?: string[]
  extraDirty?: boolean
}>(), {
  tagsMatch: 'or',
  tagsNested: true,
  selectedTags: () => [],
  extraDirty: false
})

const tagStore = useMusicTagStore()
const tagsMatch = ref<'and' | 'or'>(props.tagsMatch)
const tagsNested = ref(props.tagsNested)
const tickedByGroup = reactive<Record<string, string[]>>({})

const activeTags = (tags: IMusicTag[]): IMusicTag[] => tags.filter(tag => tag.is_active)

const toTreeNodes = (tags: IMusicTag[]): TagTreeNode[] => {
  return activeTags(tags).map(tag => {
    const children = tag.tags?.length ? toTreeNodes(tag.tags) : undefined

    return {
      id: tag.id,
      label: tag.name,
      ...(children?.length ? { children } : {})
    }
  })
}

const visibleGroups = computed(() => (
  tagStore.groups.filter(group => group.is_active)
))

const groupTrees = computed<GroupTree[]>(() => {
  const groups: GroupTree[] = visibleGroups.value.map((group: IMusicTagGroup) => {
    const nodes = toTreeNodes(tagStore.tagsByGroup(group.id))

    return {
      id: group.id,
      name: group.name,
      caption: `${countNodes(nodes)} tags`,
      nodes
    }
  })

  const ungrouped = toTreeNodes(tagStore.ungroupedTags)
  if (ungrouped.length) {
    groups.push({
      id: 'ungrouped',
      name: 'Other',
      caption: `${countNodes(ungrouped)} tags`,
      nodes: ungrouped
    })
  }

  return groups
})

const selectedTagIds = computed(() => (
  Object.values(tickedByGroup).flat().filter(Boolean)
))

const selectedCount = computed(() => selectedTagIds.value.length)

function countNodes (nodes: TagTreeNode[]): number {
  return nodes.reduce((sum, node) => sum + 1 + countNodes(node.children ?? []), 0)
}

function ensureGroupKeys (): void {
  for (const group of groupTrees.value) {
    if (!Array.isArray(tickedByGroup[group.id])) {
      tickedByGroup[group.id] = []
    }
  }
}

function applySelected (ids: string[]): void {
  const allowed = new Set(ids)
  ensureGroupKeys()
  for (const group of groupTrees.value) {
    const nodeIds = collectIds(group.nodes)
    tickedByGroup[group.id] = nodeIds.filter(id => allowed.has(id))
  }
}

function collectIds (nodes: TagTreeNode[]): string[] {
  return nodes.flatMap(node => [node.id, ...collectIds(node.children ?? [])])
}

function emitChange (): void {
  emit('change', {
    tags: [...selectedTagIds.value],
    tagsMatch: tagsMatch.value,
    tagsNested: tagsNested.value
  })
}

function scheduleEmit (): void {
  if (emitTimer) {
    clearTimeout(emitTimer)
  }

  emitTimer = setTimeout(() => {
    emitChange()
  }, 300)
}

function onGroupTicked (groupId: string, ids: string[]): void {
  tickedByGroup[groupId] = [...ids]
  scheduleEmit()
}

function resetFilter (): void {
  Object.keys(tickedByGroup).forEach(key => {
    tickedByGroup[key] = []
  })
  tagsMatch.value = 'or'
  tagsNested.value = true
  emit('reset')
  scheduleEmit()
}

let emitTimer: ReturnType<typeof setTimeout> | undefined

watch(groupTrees, () => {
  ensureGroupKeys()
}, { immediate: true })

watch([tagsMatch, tagsNested], () => {
  scheduleEmit()
})

onMounted(async () => {
  if (!tagStore.groups.length || !tagStore.tags.length) {
    await tagStore.loadAll()
  }
  applySelected(props.selectedTags)
})

onUnmounted(() => {
  if (emitTimer) {
    clearTimeout(emitTimer)
  }
})
</script>

<style lang="scss" scoped>
.music-filter {
  position: relative;
  min-height: 120px;
}
</style>
