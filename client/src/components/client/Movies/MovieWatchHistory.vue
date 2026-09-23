<template>
  <section v-if="isLoading || posts.length" class="movie-watch-history">
    <div class="movie-watch-history__title">Просмотры</div>

    <q-markup-table v-if="posts.length" class="movie-watch-history__table" flat wrap-cells>
      <thead>
        <tr>
          <th class="text-left">Название</th>
          <th class="text-left movie-watch-history__datetime">Дата</th>
          <th v-if="isTvSeries" class="text-left">Прогресс</th>
          <th class="text-left">Заметка</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="post in posts" :key="post.id">
          <td>{{ post.title || '—' }}</td>
          <td class="movie-watch-history__datetime">{{ formatPostDateTime(post) }}</td>
          <td v-if="isTvSeries" class="movie-watch-history__progress">
            {{ formatSeriesWatchProgress(post.watch) || '—' }}
          </td>
          <td class="movie-watch-history__content">{{ post.content || '—' }}</td>
        </tr>
      </tbody>
    </q-markup-table>

    <div v-else class="movie-watch-history__skeleton">
      <q-skeleton type="text" width="40%" />
      <q-skeleton type="text" width="100%" />
      <q-skeleton type="text" width="90%" />
    </div>
  </section>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { handleApiError } from 'src/utils/jsonapi'
import { normalizePosts } from 'src/api/mappers/post.response.mapper'
import { formatPostDateTime } from 'src/utils/LifeLog/post'
import { formatSeriesWatchProgress } from 'src/utils/LifeLog/seriesWatch'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { IPost } from 'src/types'

const props = defineProps<{
  movieId: string
  movieType?: MovieTypeEnum
}>()

const isTvSeries = computed(() => props.movieType === MovieTypeEnum.TV_SERIES)

const posts = ref<IPost[]>([])
const isLoading = ref(false)
let requestId = 0

async function loadWatchHistory(movieId: string): Promise<void> {
  requestId += 1
  const currentRequest = requestId

  isLoading.value = true
  posts.value = []

  try {
    const response = await postApi.getPosts({
      movie_id: movieId
    })

    if (currentRequest !== requestId) {
      return
    }

    posts.value = normalizePosts(mapResponse(response) as IPost[])
  } catch (error) {
    if (currentRequest !== requestId) {
      return
    }

    posts.value = []
    handleApiError(error)
  } finally {
    if (currentRequest === requestId) {
      isLoading.value = false
    }
  }
}

watch(
  () => props.movieId,
  (movieId) => {
    void loadWatchHistory(movieId)
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.movie-watch-history {
  margin-top: 2rem;

  &__title {
    margin-bottom: 12px;
    font-size: 18px;
    font-weight: 600;
    color: #282f53;
  }

  &__table {
    width: 100%;
    background: #fff;
    border: 1px solid rgba(40, 47, 83, 0.08);
    border-radius: 12px;
    overflow: hidden;

    :deep(th) {
      font-size: 13px;
      font-weight: 600;
      color: #777a8f;
      background: rgba(40, 47, 83, 0.03);
    }

    :deep(td),
    :deep(th) {
      padding: 10px 14px;
      vertical-align: top;
    }

    :deep(td) {
      font-size: 14px;
      color: #282f53;
    }
  }

  &__datetime {
    width: 160px;
    white-space: nowrap;
  }

  &__content {
    white-space: pre-line;
    word-break: break-word;
  }

  &__progress {
    min-width: 180px;
    white-space: normal;
  }

  &__skeleton {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
}

@media (max-width: 700px) {
  .movie-watch-history__datetime {
    width: auto;
    white-space: normal;
  }
}
</style>
