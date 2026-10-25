<template>
  <div class="ll-task-subject">
    <div class="ll-task-subject__icon">
      <q-icon name="task_alt" size="22px" />
    </div>
    <div class="ll-task-subject__body">
      <div class="ll-task-subject__title">
        {{ task.title }}
      </div>
      <div
        v-if="task.task_list_title"
        class="ll-task-subject__list"
      >
        <q-icon name="folder_open" size="14px" />
        <span class="ellipsis">{{ task.task_list_title }}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IPostTaskSubject } from 'src/types'

defineProps<{
  task: IPostTaskSubject
}>()
</script>

<style scoped lang="scss">
.ll-task-subject {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  background: linear-gradient(135deg, #f7fff9 0%, #ffffff 55%);

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    flex: none;
    border-radius: 10px;
    background: rgba(33, 186, 69, 0.12);
    color: #21ba45;
  }

  &__body {
    min-width: 0;
    flex: 1;
  }

  &__title {
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.35;
    color: #1f2937;
  }

  &__list {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
    color: #6b7280;
    font-size: 13px;
  }
}
</style>
