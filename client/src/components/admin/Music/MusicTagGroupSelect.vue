<template>
  <div class="q-gutter-md">
    <q-select
      v-for="group in groups"
      :key="group.id"
      :model-value="idsForGroup(group.id)"
      :options="optionsForGroup(group.id)"
      :label="group.name"
      option-value="id"
      option-label="label"
      emit-value
      map-options
      use-input
      use-chips
      multiple
      outlined
      dense
      input-debounce="0"
      @filter="(val, update) => filterGroup(group.id, val, update)"
      @update:model-value="ids => setGroupIds(group.id, Array.isArray(ids) ? ids.map(String) : [])"
    />
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import { IMusicTag, IMusicTagGroup } from 'src/types'

interface TagOption {
  id: string
  label: string
  groupId: string
}

const props = defineProps<{
  modelValue: string[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string[]]
}>()

const tagStore = useMusicTagStore()
const optionFilter = ref<Record<string, string>>({})

const groups = computed(() => tagStore.groups.filter(group => group.is_active))

function flattenOptions (tags: IMusicTag[], groupId: string, depth = 0): TagOption[] {
  return tags
    .filter(tag => tag.is_active)
    .flatMap(tag => [
      {
        id: tag.id,
        groupId,
        label: `${depth ? `${'— '.repeat(depth)}` : ''}${tag.name}`
      },
      ...flattenOptions(tag.tags ?? [], groupId, depth + 1)
    ])
}

function optionsForGroup (groupId: string): TagOption[] {
  const needle = (optionFilter.value[groupId] ?? '').toLowerCase()
  const options = flattenOptions(tagStore.tagsByGroup(groupId), groupId)

  if (!needle) {
    return options
  }

  return options.filter(option => option.label.toLowerCase().includes(needle))
}

function idsForGroup (groupId: string): string[] {
  const allowed = new Set(flattenOptions(tagStore.tagsByGroup(groupId), groupId).map(option => option.id))

  return props.modelValue.filter(id => allowed.has(id))
}

function setGroupIds (groupId: string, ids: string[]): void {
  const allowed = new Set(flattenOptions(tagStore.tagsByGroup(groupId), groupId).map(option => option.id))
  const rest = props.modelValue.filter(id => !allowed.has(id))
  const next = [...ids]

  emit('update:modelValue', [...rest, ...next])
}

function filterGroup (groupId: string, val: string, update: (fn: () => void) => void): void {
  update(() => {
    optionFilter.value = { ...optionFilter.value, [groupId]: val }
  })
}

watch(groups, (items: IMusicTagGroup[]) => {
  items.forEach(group => {
    if (optionFilter.value[group.id] === undefined) {
      optionFilter.value[group.id] = ''
    }
  })
}, { immediate: true })
</script>
