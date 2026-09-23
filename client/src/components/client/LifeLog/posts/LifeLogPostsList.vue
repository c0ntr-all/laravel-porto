<template>
  <div class="ll-posts-list">
    <q-inner-loading :showing="isLoading && !posts.length">
      <q-spinner color="primary" size="36px" />
    </q-inner-loading>

    <template v-if="!isLoading || posts.length">
      <AppNoResultsPlug
        v-if="!posts.length"
        title="Постов пока нет"
        body="Создайте первый пост или измените фильтры"
      />

      <div
        v-else
        ref="containerRef"
        class="ll-posts-list__layout"
      >
        <div class="ll-posts-list__main">
          <div
            ref="itemsRef"
            class="ll-posts-list__items"
            :class="`ll-posts-list__items--${viewMode}`"
          >
            <div
              v-for="post in posts"
              :key="post.id"
              :ref="element => setPostRef(post.id, element)"
              class="ll-posts-list__item"
            >
              <LifeLogPostMovieCard
                v-if="viewMode === 'expanded' && isMovieWatchPost(post)"
                :post="post"
              />
              <LifeLogPostCard
                v-else-if="viewMode === 'expanded'"
                :post="post"
              />
              <LifeLogPostRow
                v-else
                :post="post"
                :expanded="isPostExpanded(post.id)"
                @toggle-expand="handleToggleExpand(post.id)"
              />
            </div>
          </div>

          <div
            v-if="hasMorePosts"
            ref="sentinel"
            class="ll-posts-list__sentinel"
          />

          <div
            v-if="isLoadingMore"
            class="ll-posts-list__loader flex justify-center q-py-md"
          >
            <q-spinner color="primary" size="2em" />
          </div>
        </div>

        <div
          class="ll-posts-list__rails"
          :style="{ height: `${containerHeight}px` }"
        >
          <LifeLogPresetRails
            :posts="posts"
            :presets="presets"
            :anchors="anchors"
            :height="containerHeight"
          />
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, watch } from 'vue'
import { IPost, IPreset, LifeLogViewMode } from 'src/types'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { usePostRailAnchors } from 'src/composables/client/Lifelog/usePostRailAnchors'
import LifeLogPostCard from 'src/components/client/LifeLog/posts/LifeLogPostCard.vue'
import LifeLogPostMovieCard from 'src/components/client/LifeLog/posts/LifeLogPostMovieCard.vue'
import LifeLogPostRow from 'src/components/client/LifeLog/posts/LifeLogPostRow.vue'
import { isMovieWatchPost } from 'src/utils/LifeLog/post'
import LifeLogPresetRails from 'src/components/client/LifeLog/posts/LifeLogPresetRails.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  posts: IPost[]
  presets: IPreset[]
  viewMode: LifeLogViewMode
  isLoading: boolean
  isLoadingMore: boolean
  hasMorePosts: boolean
  isPostExpanded: (postId: string) => boolean
  togglePostExpanded: (postId: string) => void
}>()

const emit = defineEmits<{
  'load-more': []
}>()

const { sentinel } = useScrollSentinel(
  () => emit('load-more'),
  () => props.hasMorePosts && !props.isLoadingMore && !props.isLoading
)

const postsRef = computed(() => props.posts)
const { containerRef, itemsRef, anchors, containerHeight, setPostRef, measure } = usePostRailAnchors(postsRef)

function handleToggleExpand(postId: string) {
  props.togglePostExpanded(postId)
  void nextTick(measure)
}

watch(
  () => props.viewMode,
  () => {
    void nextTick(measure)
  }
)
</script>

<style scoped lang="scss">
.ll-posts-list {
  position: relative;
  min-height: 120px;

  &__layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 56px;
    gap: 8px;
    align-items: start;
  }

  &__main {
    min-width: 0;
  }

  &__rails {
    position: relative;
    align-self: start;
  }

  &__items {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0;

    &--compact {
      gap: 8px;
    }
  }

  &__item {
    min-width: 0;
  }

  &__sentinel {
    height: 1px;
  }
}
</style>
