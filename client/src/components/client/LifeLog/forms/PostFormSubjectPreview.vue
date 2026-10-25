<template>
  <div
    v-if="task || album"
    class="post-form-subject-preview"
  >
    <div class="post-form-subject-preview__head">
      <span class="text-caption text-grey-7">Прикреплено</span>
      <q-btn
        flat
        dense
        no-caps
        color="grey-7"
        icon="close"
        label="Убрать"
        @click="emit('clear')"
      />
    </div>

    <LifeLogPostTaskSubject
      v-if="task"
      :task="task"
    />
    <LifeLogPostAlbumSubject
      v-else-if="album"
      :album="album"
    />
  </div>
</template>

<script lang="ts" setup>
import { IPostAlbumSubject, IPostTaskSubject } from 'src/types'
import LifeLogPostTaskSubject from 'src/components/client/LifeLog/posts/LifeLogPostTaskSubject.vue'
import LifeLogPostAlbumSubject from 'src/components/client/LifeLog/posts/LifeLogPostAlbumSubject.vue'

defineProps<{
  task?: IPostTaskSubject | null
  album?: IPostAlbumSubject | null
}>()

const emit = defineEmits<{
  clear: []
}>()
</script>

<style scoped lang="scss">
.post-form-subject-preview {
  margin-top: 12px;

  &__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
  }
}
</style>
