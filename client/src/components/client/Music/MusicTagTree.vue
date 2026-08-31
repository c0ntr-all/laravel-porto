<template>
  <div class="tag-tree q-gutter-xs">
    <div
      v-for="tag in visibleTags"
      :key="tag.id"
    >
      <q-chip
        clickable
        outline
        color="primary"
        text-color="primary"
        @click="$emit('select', tag)"
      >
        {{ tag.name }}
        <q-tooltip v-if="tag.description">
          {{ tag.description }}
        </q-tooltip>
      </q-chip>
      <div v-if="tag.tags?.length" class="tag-tree__children q-mt-xs">
        <MusicTagTree
          :tags="tag.tags"
          @select="$emit('select', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IMusicTag } from 'src/types'

defineOptions({ name: 'MusicTagTree' })

const props = defineProps<{
  tags: IMusicTag[]
}>()

defineEmits<{
  select: [tag: IMusicTag]
}>()

const visibleTags = computed(() => props.tags.filter(tag => tag.is_active !== false))
</script>

<style lang="scss" scoped>
.tag-tree__children {
  margin-left: 20px;
  padding-left: 12px;
  border-left: 1px solid #e0e0e0;
}
</style>
