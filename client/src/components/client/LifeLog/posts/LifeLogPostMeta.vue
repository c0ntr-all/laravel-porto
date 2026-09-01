<template>
  <div class="ll-post-meta row items-center justify-between q-gutter-sm">
    <div class="ll-post-meta__tags row items-center q-gutter-xs">
      <LifeLogTag
        v-for="tag in post.tags"
        :key="tag.id"
        :tag="tag"
        dense
        color="primary"
        text-color="white"
      />
      <span v-if="!post.tags.length" class="text-caption text-grey-6">
        Без тегов
      </span>
    </div>
    <div class="ll-post-meta__date text-caption text-grey-7">
      {{ formattedDate }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IPost } from 'src/types'
import { humanDatetime } from 'src/utils/datetime'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import { formatPostDateTime } from 'src/utils/LifeLog/post'

const props = defineProps<{
  post: IPost
}>()

const formattedDate = computed(() => {
  const value = formatPostDateTime(props.post)
  return humanDatetime(value)
})
</script>

<style scoped lang="scss">
.ll-post-meta {
  &__tags {
    min-width: 0;
    flex: 1;
    flex-wrap: wrap;
  }

  &__date {
    flex-shrink: 0;
    white-space: nowrap;
  }
}
</style>
