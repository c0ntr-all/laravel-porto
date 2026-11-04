<template>
  <div
    v-if="enabled"
    ref="sentinel"
    class="notes-load-more"
  >
    <q-spinner
      v-if="loading"
      color="primary"
      size="20px"
    />
  </div>
</template>

<script lang="ts" setup>
import { useScrollSentinel } from 'src/composables/useScrollSentinel'

const props = defineProps<{
  loading: boolean
  enabled: boolean
}>()

const emit = defineEmits<{
  load: []
}>()

const { sentinel } = useScrollSentinel(
  () => emit('load'),
  () => props.enabled && !props.loading
)
</script>

<style lang="scss" scoped>
.notes-load-more {
  display: flex;
  justify-content: center;
  min-height: 28px;
  padding: 8px 0;
}
</style>
