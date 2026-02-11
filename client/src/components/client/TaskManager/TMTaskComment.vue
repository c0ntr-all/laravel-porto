<template>
  <q-card>
    <q-card-section class="flex justify-between">
      <span>{{ user.name }}</span>
      <time class="time">{{ comment.created_at }}</time>
    </q-card-section>
    <q-card-section>
      {{ comment.content }}
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IComment } from 'src/types'
import { useTaskStore } from 'src/stores/modules/taskStore'

const taskStore = useTaskStore()

const props = defineProps<{
  comment: IComment
}>()

const user = computed(() => taskStore.user.byId[props.comment.userIds[0]])
</script>

<style scoped lang="scss">

</style>
