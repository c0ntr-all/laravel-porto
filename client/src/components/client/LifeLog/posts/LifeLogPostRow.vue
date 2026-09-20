<template>
  <div
    class="ll-post-row"
    :class="{ 'll-post-row--pending': post.is_pending }"
  >
    <button
      type="button"
      class="ll-post-row__button row items-center no-wrap"
      @click="emit('toggle-expand')"
    >
      <AppUserAvatar
        size="28px"
        class="ll-post-row__avatar"
        :user="post.user"
      />

      <div class="col ll-post-row__main q-pl-sm">
        <div class="row items-center no-wrap">
          <div class="col ll-post-row__title ellipsis">
            {{ rowTitle }}
          </div>
          <LifeLogPostAttachmentsBadge
            class="q-mx-sm"
            :post="post"
          />
          <div class="ll-post-row__date text-caption text-grey-7">
            {{ formattedDate }}
          </div>
          <q-icon
            :name="expanded ? 'expand_less' : 'expand_more'"
            size="18px"
            color="grey-6"
            class="q-ml-xs"
          />
        </div>
      </div>
    </button>

    <div v-if="expanded" class="ll-post-row__expanded q-pt-sm">
      <LifeLogPostMovieCard v-if="isMovieWatchPost(post)" :post="post" />
      <LifeLogPostCard v-else :post="post" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IPost } from 'src/types'
import { humanDatetime } from 'src/utils/datetime'
import {
  formatPostDateTime,
  isMovieWatchPost,
  MOVIE_WATCH_POST_TITLE
} from 'src/utils/LifeLog/post'
import LifeLogPostAttachmentsBadge from 'src/components/client/LifeLog/posts/LifeLogPostAttachmentsBadge.vue'
import LifeLogPostCard from 'src/components/client/LifeLog/posts/LifeLogPostCard.vue'
import LifeLogPostMovieCard from 'src/components/client/LifeLog/posts/LifeLogPostMovieCard.vue'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'

const props = defineProps<{
  post: IPost
  expanded?: boolean
}>()

const emit = defineEmits<{
  'toggle-expand': []
}>()

const formattedDate = computed(() => humanDatetime(formatPostDateTime(props.post)))

const rowTitle = computed(() =>
  isMovieWatchPost(props.post) ? MOVIE_WATCH_POST_TITLE : props.post.title
)
</script>

<style scoped lang="scss">
.ll-post-row {
  &--pending {
    .ll-post-row__button {
      opacity: 0.72;
      border-style: dashed;
    }
  }

  &__button {
    width: 100%;
    border: 1px solid #e4e7eb;
    border-radius: 10px;
    background: #fff;
    padding: 8px 10px;
    cursor: pointer;
    text-align: left;
    transition: background-color 0.15s ease;

    &:hover {
      background: #f8fafc;
    }
  }

  &__title {
    font-size: 0.95rem;
    font-weight: 500;
    min-width: 0;
  }

  &__date {
    flex-shrink: 0;
    white-space: nowrap;
  }

  &__expanded {
    padding-left: 4px;
  }
}
</style>
