<template>
  <div v-if="counts.total" class="ll-attachments-badge row items-center no-wrap q-gutter-xs">
    <q-chip
      v-if="counts.images"
      dense
      size="sm"
      icon="image"
      color="grey-3"
      text-color="grey-9"
    >
      {{ counts.images }}
    </q-chip>
    <q-chip
      v-if="counts.videos"
      dense
      size="sm"
      icon="videocam"
      color="grey-3"
      text-color="grey-9"
    >
      {{ counts.videos }}
    </q-chip>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IPost } from 'src/types'
import { countPostAttachments } from 'src/utils/LifeLog/post'

const props = defineProps<{
  post: IPost
}>()

const counts = computed(() => countPostAttachments(props.post))
</script>
