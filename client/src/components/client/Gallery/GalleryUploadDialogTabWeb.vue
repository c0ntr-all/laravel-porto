<template>
  <q-card-section>
    <div class="q-gutter-md">
      <q-input
        v-model="file"
        label="Image link"
        outlined
      />
    </div>
  </q-card-section>
  <q-card-section>
    <q-btn
      @click="initUpload"
      icon="upload"
      color="primary"
    >
      <div class="q-ml-xs">Begin upload</div>
    </q-btn>
  </q-card-section>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'initUpload', data: boolean): void
}>()
const file = ref('')

const initUpload = () => {
  emit('initUpload', file.value, 'web')
}
</script>

<style scoped lang="scss">

</style>
