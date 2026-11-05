<template>
  <div class="ll-post-meta">
    <div class="ll-post-meta__row row items-center justify-between q-gutter-sm">
      <div class="ll-post-meta__tags row items-center q-gutter-xs">
        <LifeLogTag
          v-for="tag in post.tags"
          :key="tag.id"
          :tag="tag"
          dense
          :color="'secondary'"
          text-color="white"
        />
        <span v-if="!post.tags.length" class="text-caption text-grey-6">
          Без тегов
        </span>
      </div>
      <div class="ll-post-meta__aside row items-center no-wrap">
        <span
          v-if="commentsEnabled"
          class="ll-post-meta__comments-wrap"
        >
          <button
            type="button"
            class="ll-post-meta__comments"
            :class="{ 'is-open': commentsOpen }"
            :disabled="Boolean(post.is_pending)"
            :aria-expanded="commentsOpen"
            :aria-controls="commentsPanelId"
            :aria-label="`Комментарии: ${commentsCount}`"
            @click="toggleComments"
          >
            <q-icon
              :name="commentsOpen ? 'chat_bubble' : 'chat_bubble_outline'"
              size="15px"
            />
            <span>{{ commentsCount }}</span>
          </button>
          <q-tooltip>
            {{ post.is_pending ? 'Пост ещё сохраняется' : 'Комментарии' }}
          </q-tooltip>
        </span>
        <div class="ll-post-meta__date text-caption text-grey-7">
          {{ formattedDate }}
        </div>
      </div>
    </div>

    <LifeLogPostComments
      v-if="commentsEnabled && commentsOpen"
      :post="post"
      @close="commentsOpen = false"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { IPost } from 'src/types'
import { humanDatetime } from 'src/utils/datetime'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import LifeLogPostComments from 'src/components/client/LifeLog/posts/LifeLogPostComments.vue'
import { formatPostDateTime } from 'src/utils/LifeLog/post'

const props = withDefaults(defineProps<{
  post: IPost
  commentsEnabled?: boolean
}>(), {
  commentsEnabled: true
})

const commentsOpen = ref(false)

const formattedDate = computed(() => {
  const value = formatPostDateTime(props.post)
  return humanDatetime(value)
})

const commentsCount = computed(() => props.post.comments_count ?? 0)
const commentsPanelId = computed(() => `ll-post-comments-${props.post.id}`)

function toggleComments() {
  if (props.post.is_pending) {
    return
  }

  commentsOpen.value = !commentsOpen.value
}

watch(
  () => props.commentsEnabled,
  (enabled) => {
    if (!enabled) {
      commentsOpen.value = false
    }
  }
)
</script>

<style scoped lang="scss">
.ll-post-meta {
  &__tags {
    min-width: 0;
    flex: 1;
    flex-wrap: wrap;
  }

  &__aside {
    flex-shrink: 0;
    gap: 6px;
  }

  &__comments-wrap {
    display: inline-flex;
  }

  &__comments {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 26px;
    padding: 0 8px;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    cursor: pointer;

    &:hover:not(:disabled) {
      background: #f1f5f9;
      color: #334155;
    }

    &.is-open {
      background: #eef2ff;
      color: var(--q-primary);
    }

    &:disabled {
      cursor: default;
      opacity: 0.55;
    }
  }

  &__date {
    flex-shrink: 0;
    white-space: nowrap;
  }
}
</style>
