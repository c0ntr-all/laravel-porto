<template>
  <div
    class="ll-posts-skeleton"
    :class="`ll-posts-skeleton--${viewMode}`"
  >
    <div
      v-for="index in count"
      :key="index"
      class="ll-posts-skeleton__item"
    >
      <article
        v-if="viewMode === 'expanded'"
        class="ll-posts-skeleton__card"
      >
        <div class="ll-posts-skeleton__card-header row items-start no-wrap">
          <q-skeleton type="QAvatar" size="40px" />
          <div class="col q-pl-sm">
            <q-skeleton type="text" width="55%" />
            <q-skeleton type="text" width="30%" class="q-mt-xs" />
          </div>
        </div>
        <div class="ll-posts-skeleton__card-body">
          <q-skeleton type="text" width="95%" />
          <q-skeleton type="text" width="88%" />
          <q-skeleton type="text" width="72%" />
        </div>
        <div class="ll-posts-skeleton__card-footer row items-center justify-between">
          <q-skeleton type="text" width="35%" />
          <q-skeleton type="text" width="22%" />
        </div>
      </article>

      <div
        v-else
        class="ll-posts-skeleton__row row items-center no-wrap"
      >
        <q-skeleton type="QAvatar" size="28px" />
        <div class="col q-pl-sm">
          <q-skeleton type="text" width="70%" />
        </div>
        <q-skeleton type="text" width="72px" class="q-ml-sm" />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { LifeLogViewMode } from 'src/types'

withDefaults(defineProps<{
  viewMode: LifeLogViewMode
  count?: number
}>(), {
  count: 5
})
</script>

<style scoped lang="scss">
.ll-posts-skeleton {
  display: flex;
  flex-direction: column;
  gap: 12px;

  &--compact {
    gap: 8px;
  }

  &__card {
    background: #fff;
    border: 1px solid #e4e7eb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
  }

  &__card-header {
    padding: 14px 14px 0;
  }

  &__card-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 14px;
  }

  &__card-footer {
    padding: 0 14px 14px;
  }

  &__row {
    width: 100%;
    border: 1px solid #e4e7eb;
    border-radius: 10px;
    background: #fff;
    padding: 8px 10px;
  }
}
</style>
