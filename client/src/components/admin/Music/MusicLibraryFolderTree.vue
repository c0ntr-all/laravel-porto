<template>
  <div class="relative-position">
    <div class="text-subtitle1 q-mb-sm">Music library</div>
    <q-banner
      v-if="errorMessage"
      class="q-mb-sm"
      dense
      rounded
    >
      {{ errorMessage }}
    </q-banner>
    <q-scroll-area
      v-if="nodes.length"
      class="music-library-folder-tree"
      style="height: 360px"
    >
      <q-tree
        :nodes="nodes"
        node-key="path"
        selected-color="primary"
        :selected="selectedPath"
        :expanded="expanded"
        lazy
        dense
        no-selection-unset
        @update:selected="onSelect"
        @update:expanded="onExpanded"
        @lazy-load="onLazyLoad"
      >
        <template #default-header="prop">
          <div class="row items-center no-wrap">
            <q-icon
              :key="`${prop.node.path}:${prop.node.uploaded}`"
              :name="prop.node.uploaded ? 'check_box' : 'check_box_outline_blank'"
              :color="prop.node.uploaded ? 'positive' : 'grey-5'"
              size="xs"
              class="q-mr-sm"
            />
            <div class="ellipsis">{{ prop.node.label }}</div>
          </div>
        </template>
      </q-tree>
    </q-scroll-area>
    <q-inner-loading :showing="isLoading">
      <q-spinner size="2em" color="primary" />
    </q-inner-loading>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref, watch } from 'vue'
import { useMusicUploadStore } from 'src/stores/modules/musicUploadStore'
import {
  IMusicLibraryFolder,
  IMusicLibraryFolderTreeNode
} from 'src/types'

const selectedPath = defineModel<string | null>({ default: null })

const store = useMusicUploadStore()
const nodes = ref<IMusicLibraryFolderTreeNode[]>([])
const expanded = ref<string[]>([])
const isLoading = ref(false)
const errorMessage = ref<string | null>(null)

function normalizePath(path: string): string {
  return path.replace(/\//g, '\\').replace(/\\+$/, '').toLowerCase()
}

function parentPath(path: string): string | undefined {
  const normalized = path.replace(/\//g, '\\').replace(/\\+$/, '')
  const index = normalized.lastIndexOf('\\')

  if (index <= 2) {
    return undefined
  }

  return normalized.slice(0, index)
}

function toNode(folder: IMusicLibraryFolder): IMusicLibraryFolderTreeNode {
  return {
    path: folder.path,
    label: folder.name,
    uploaded: folder.uploaded,
    lazy: folder.has_children
  }
}

function mapNodes(
  list: IMusicLibraryFolderTreeNode[],
  update: (node: IMusicLibraryFolderTreeNode) => IMusicLibraryFolderTreeNode
): IMusicLibraryFolderTreeNode[] {
  return list.map(node => {
    const next = update(node)
    const children = next.children
      ? mapNodes(next.children, update)
      : node.children

    if (next === node && children === node.children) {
      return node
    }

    return {
      ...next,
      children
    }
  })
}

function markUploaded(path: string): void {
  const target = normalizePath(path)

  nodes.value = mapNodes(nodes.value, node => {
    if (node.uploaded || normalizePath(node.path) !== target) {
      return node
    }

    return {
      ...node,
      uploaded: true
    }
  })
}

function applyUploadedFlags(flags: Map<string, boolean>): void {
  nodes.value = mapNodes(nodes.value, node => {
    const uploaded = flags.get(normalizePath(node.path))
    if (uploaded === undefined || uploaded === node.uploaded) {
      return node
    }

    return {
      ...node,
      uploaded
    }
  })
}

function onSelect(path: string) {
  if (path) {
    selectedPath.value = path
  }
}

function onExpanded(value: string[]) {
  expanded.value = value
}

async function onLazyLoad(info: {
  node: IMusicLibraryFolderTreeNode
  done: (children?: IMusicLibraryFolderTreeNode[]) => void
  fail: () => void
}) {
  const result = await store.getLibraryFolders(info.node.path)

  if (!result) {
    info.fail()
    return
  }

  info.done(result.folders.map(toNode))
}

async function syncImportedFolder(path: string): Promise<void> {
  markUploaded(path)

  const parent = parentPath(path)
  const [parentResult, selfResult] = await Promise.all([
    store.getLibraryFolders(parent, { silent: true }),
    store.getLibraryFolders(path, { silent: true })
  ])

  const flags = new Map<string, boolean>()

  for (const result of [parentResult, selfResult]) {
    if (!result) {
      continue
    }

    result.folders.forEach(folder => {
      flags.set(normalizePath(folder.path), folder.uploaded)
    })
    flags.set(normalizePath(result.path), result.uploaded)
  }

  applyUploadedFlags(flags)
}

onMounted(async () => {
  isLoading.value = true
  errorMessage.value = null

  try {
    const result = await store.getLibraryFolders()
    if (!result) {
      errorMessage.value = 'Could not load the music library folders.'
      return
    }

    nodes.value = [{
      path: result.path,
      label: result.name,
      uploaded: result.uploaded,
      lazy: false,
      children: result.folders.map(toNode)
    }]
    expanded.value = result.path ? [result.path] : []
  } finally {
    isLoading.value = false
  }
})

watch(
  () => store.importedFolderTick,
  () => {
    const path = store.importedFolderPath
    if (!path) {
      return
    }

    void syncImportedFolder(path)
  }
)
</script>
