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
        ref="containerRef"
        class="ll-posts-list__layout"
      >
        <div
          class="ll-posts-list__items"
          :class="`ll-posts-list__items--${viewMode}`"
        >
          <div
            v-for="post in posts"
            :key="post.id"
            :ref="element => setPostRef(post.id, element)"
            class="ll-posts-list__item"
          >
            <LifeLogPostCard
              v-if="viewMode === 'expanded'"
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
import { usePostRailAnchors } from 'src/composables/client/Lifelog/usePostRailAnchors'
import LifeLogPostCard from 'src/components/client/LifeLog/posts/LifeLogPostCard.vue'
import LifeLogPostRow from 'src/components/client/LifeLog/posts/LifeLogPostRow.vue'
import LifeLogPresetRails from 'src/components/client/LifeLog/posts/LifeLogPresetRails.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  posts: IPost[]
  presets: IPreset[]
  viewMode: LifeLogViewMode
  isLoading: boolean
  isPostExpanded: (postId: string) => boolean
  togglePostExpanded: (postId: string) => void
}>()

const postsRef = computed(() => props.posts)
const { containerRef, anchors, containerHeight, setPostRef, measure } = usePostRailAnchors(postsRef)

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

  &__rails {
    position: relative;
    min-height: 100%;
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
}
</style>
