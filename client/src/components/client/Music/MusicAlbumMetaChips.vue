<template>
  <div v-if="typeLabel || edition" class="album-meta-chips" :class="{ 'q-gutter-xs': true }">
    <q-chip
      v-if="typeLabel"
      size="sm"
      dense
      outline
      color="deep-purple"
      class="album-meta-chips__type"
    >
      {{ typeLabel }}
      <q-tooltip>Version</q-tooltip>
    </q-chip>
    <q-chip
      v-if="edition"
      size="sm"
      dense
      outline
      color="orange"
      class="album-meta-chips__edition"
    >
      {{ edition }}
      <q-tooltip>Edition</q-tooltip>
    </q-chip>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IAlbumType } from 'src/types'
import { formatAlbumTypeLabel } from 'src/utils/albumMeta'

const props = defineProps<{
  albumType?: IAlbumType | string | null
  edition?: string | null
}>()

const typeLabel = computed(() => {
  if (!props.albumType) {
    return ''
  }

  if (typeof props.albumType === 'string') {
    return formatAlbumTypeLabel(props.albumType)
  }

  return props.albumType.label || formatAlbumTypeLabel(props.albumType.name)
})
</script>
