<template>
  <div class="ll-posts-list">
    <q-inner-loading :showing="isLoading">
      <q-spinner color="primary" size="36px" />
    </q-inner-loading>

    <template v-if="!isLoading">
      <AppNoResultsPlug
        v-if="!posts.length"
        title="Постов пока нет"
        body="Создайте первый пост или измените фильтры"
      />

      <div
        v-else
        class="ll-posts-list__items"
        :class="`ll-posts-list__items--${viewMode}`"
      >
        <template v-if="viewMode === 'expanded'">
          <LifeLogPostCard
            v-for="post in posts"
            :key="post.id"
            :post="post"
          />
        </template>

        <template v-else>
          <LifeLogPostRow
            v-for="post in posts"
            :key="post.id"
            :post="post"
            :expanded="isPostExpanded(post.id)"
            @toggle-expand="togglePostExpanded(post.id)"
          />
        </template>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { IPost, LifeLogViewMode } from 'src/types'
import LifeLogPostCard from 'src/components/client/LifeLog/posts/LifeLogPostCard.vue'
import LifeLogPostRow from 'src/components/client/LifeLog/posts/LifeLogPostRow.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

defineProps<{
  posts: IPost[]
  viewMode: LifeLogViewMode
  isLoading: boolean
  isPostExpanded: (postId: string) => boolean
  togglePostExpanded: (postId: string) => void
}>()
</script>

<style scoped lang="scss">
.ll-posts-list {
  position: relative;
  min-height: 120px;

  &__items {
    display: flex;
    flex-direction: column;
    gap: 12px;

    &--compact {
      gap: 8px;
    }
  }
}
</style>
