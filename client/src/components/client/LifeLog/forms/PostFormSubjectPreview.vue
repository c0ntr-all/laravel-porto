<template>
  <div
    v-if="task || album || note"
    class="post-form-subject-preview"
  >
    <span class="text-caption text-grey-7">Прикреплено</span>

    <div
      v-if="task"
      class="post-form-subject-preview__item"
    >
      <LifeLogPostTaskSubject :task="task" />
      <q-btn
        flat
        dense
        round
        color="grey-7"
        icon="close"
        aria-label="Убрать задачу"
        @click="emit('clear', 'task')"
      />
    </div>

    <div
      v-if="album"
      class="post-form-subject-preview__item"
    >
      <LifeLogPostAlbumSubject :album="album" />
      <q-btn
        flat
        dense
        round
        color="grey-7"
        icon="close"
        aria-label="Убрать альбом"
        @click="emit('clear', 'album')"
      />
    </div>

    <div
      v-if="note"
      class="post-form-subject-preview__item"
    >
      <LifeLogPostNoteSubject :note="note" />
      <q-btn
        flat
        dense
        round
        color="grey-7"
        icon="close"
        aria-label="Убрать заметку"
        @click="emit('clear', 'note')"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IPostAlbumSubject, IPostNoteSubject, IPostTaskSubject } from 'src/types'
import LifeLogPostTaskSubject from 'src/components/client/LifeLog/posts/LifeLogPostTaskSubject.vue'
import LifeLogPostAlbumSubject from 'src/components/client/LifeLog/posts/LifeLogPostAlbumSubject.vue'
import LifeLogPostNoteSubject from 'src/components/client/LifeLog/posts/LifeLogPostNoteSubject.vue'

defineProps<{
  task?: IPostTaskSubject | null
  album?: IPostAlbumSubject | null
  note?: IPostNoteSubject | null
}>()

const emit = defineEmits<{
  clear: [kind: 'task' | 'album' | 'note']
}>()
</script>

<style scoped lang="scss">
.post-form-subject-preview {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 12px;

  &__item {
    display: flex;
    align-items: flex-start;
    gap: 8px;

    > :first-child {
      flex: 1;
      min-width: 0;
    }
  }
}
</style>
