<template>
  <article
    class="ll-post-card ll-post-movie-card"
    :class="{
      'll-post-card--pending': post.is_pending,
      'll-post-card--start-preset': isPostStartPreset,
      'll-post-card--end-preset': isPostEndPreset
    }"
  >
    <header class="ll-post-card__header row items-start no-wrap">
      <AppUserAvatar
        class="ll-post-card__avatar"
        :user="post.user"
        size="40px"
      >
        <q-tooltip>{{ post.user.email }}</q-tooltip>
      </AppUserAvatar>

      <div class="col q-pl-sm">
        <div class="row items-start no-wrap">
          <div class="col">
            <h3 class="ll-post-card__title">{{ cardTitle }}</h3>
          </div>
          <q-btn
            flat
            round
            dense
            color="grey-7"
            icon="more_vert"
          >
            <q-menu cover auto-close>
              <q-list dense>
                <q-item
                  v-for="action in actions"
                  :key="action.name"
                  clickable
                  @click="action.fn"
                >
                  <q-item-section avatar>
                    <q-icon :name="action.icon" size="xs" />
                  </q-item-section>
                  <q-item-section>{{ action.label }}</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </div>
    </header>

    <section class="ll-post-card__section ll-post-movie-card__body">
      <MovieCardHorizontal :movie="displayMovie" />

      <div
        v-if="seriesWatchText"
        class="ll-post-movie-card__watch"
      >
        <div class="ll-post-movie-card__watch-label text-caption text-grey-7">
          Прогресс просмотра
        </div>
        <div class="ll-post-movie-card__watch-value">
          {{ seriesWatchText }}
        </div>
      </div>
    </section>

    <footer class="ll-post-card__section ll-post-card__footer">
      <LifeLogPostMeta :post="post" />
    </footer>

    <q-dialog v-model="showEditPostModal">
      <PostFormMovieUpdate
        v-if="showEditPostModal"
        :post="post"
        @success="showEditPostModal = false"
      />
    </q-dialog>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IPost } from 'src/types'
import { IMovie } from 'src/types/Movie'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { useLifeLogPostActions } from 'src/composables/client/Lifelog/useLifeLogPostActions'
import { movieWatchPostTitle } from 'src/utils/LifeLog/post'
import {
  formatSeriesWatchProgress,
  isSeriesWatchPost
} from 'src/utils/LifeLog/seriesWatch'
import LifeLogPostMeta from 'src/components/client/LifeLog/posts/LifeLogPostMeta.vue'
import PostFormMovieUpdate from 'src/components/client/LifeLog/forms/PostFormMovieUpdate.vue'
import MovieCardHorizontal from 'src/components/client/Movies/MovieCardHorizontal.vue'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'

const props = defineProps<{
  post: IPost
}>()

const {
  showEditPostModal,
  isPostStartPreset,
  isPostEndPreset,
  actions
} = useLifeLogPostActions(props.post)

const cardTitle = computed(() => movieWatchPostTitle(props.post))

const seriesWatchText = computed(() => {
  if (!isSeriesWatchPost(props.post)) {
    return ''
  }

  return formatSeriesWatchProgress(props.post.watch)
})

const displayMovie = computed((): IMovie => {
  if (props.post.movie) {
    return props.post.movie
  }

  return {
    id: props.post.id,
    kp_id: 0,
    title: props.post.title?.trim() || '—',
    description: null,
    short_description: null,
    year: 0,
    type: MovieTypeEnum.MOVIE,
    cover: null,
    kp_rating: null,
    kp_img: null,
    kp_imported_at: null,
    created_at: null,
    updated_at: null,
    genres: [],
    countries: [],
    actors_count: 0,
    folder_slugs: [],
    folder_ids: [],
    credits: []
  }
})
</script>

<style scoped lang="scss">
.ll-post-card {
  position: relative;
  background: #fff;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);

  &--pending {
    opacity: 0.72;
    border-style: dashed;
  }

  &__header {
    padding: 14px 14px 0;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    line-height: 1.35;
    font-weight: 600;
  }

  &__section {
    padding: 12px 14px 0;
  }

  &__footer {
    padding-bottom: 14px;
    margin-top: 4px;
  }

  &--start-preset,
  &--end-preset {
    outline: 2px solid var(--q-primary);
    outline-offset: 0;
  }

  &--start-preset::after,
  &--end-preset::after {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    background: var(--q-primary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  &--start-preset::after {
    content: 'start';
  }

  &--end-preset::after {
    content: 'end';
  }
}

.ll-post-movie-card {
  &__body {
    padding-top: 10px;
  }

  &__watch {
    margin-top: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e8ebf0;
  }

  &__watch-label {
    margin-bottom: 4px;
  }

  &__watch-value {
    font-size: 14px;
    line-height: 1.45;
    color: #334155;
  }
}
</style>
