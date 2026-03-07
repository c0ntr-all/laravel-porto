<template>
  <q-card class="task-history-item" dense>
    <q-card-section class="flex justify-between" style="width: 100%">
      <div class="task-history-item__content">
        <q-avatar
          class="task-history-item__avatar"
          color="primary"
          text-color="white"
          size="sm"
        >
          {{ userAvatar }}
          <q-tooltip>
            {{ user.email }}
          </q-tooltip>
        </q-avatar>
        <span>{{ user.name }}</span>
        <span>{{ log.event_type }} задачу</span>
      </div>
      <div class="task-history-item__time">
        <time>{{ log.created_at }}</time>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IUseCaseLog } from 'src/types'
import { useTaskStore } from 'src/stores/modules/taskStore'

const taskStore = useTaskStore()

const props = defineProps<{
  log: IUseCaseLog
}>()

const user = computed(() => taskStore.users.byId[props.log.userId])
const userAvatar = computed(() => user.value.name.substring(0, 1))
</script>

<style lang="scss" scoped>
.task-history-item {
  display: flex;
  column-gap: .5rem;

  &__content {
    display: flex;
    align-items: center;
    column-gap: .5rem;
  }

  &__time {
    time {
      font-size: .75rem;
      color: #757272;
    }
  }
}
</style>
