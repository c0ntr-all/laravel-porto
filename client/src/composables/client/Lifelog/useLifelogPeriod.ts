import { computed } from 'vue'
import { usePostStore } from 'src/stores/modules/postStore'
import { IPeriodModel, IPost } from 'src/types'

export default function useLifelogPeriods() {
  const postStore = usePostStore()

  const startPeriodPostId = computed(() => postStore.startPeriodPostId)
  const endPeriodPostId = computed(() => postStore.endPeriodPostId)
  const startPeriodPost = computed<IPost>(() =>
    postStore.posts.find(post => post.id === startPeriodPostId.value)
  ) || null
  const endPeriodPost = computed<IPost>(() =>
    postStore.posts.find(post => post.id === endPeriodPostId.value)
  ) || null

  const setStartPeriodPostId = (postId: string): void => {
    postStore.setStartPeriodPostId(postId)
  }
  const setEndPeriodPostId = (postId: string): void => {
    postStore.setEndPeriodPostId(postId)
  }

  const resetPeriod = () => {
    postStore.setStartPeriodPostId(null)
    postStore.setEndPeriodPostId(null)
  }

  const createPeriod = (payload: IPeriodModel): Promise => {
    return postStore.createPeriod(payload)
  }

  return {
    startPeriodPostId,
    endPeriodPostId,
    startPeriodPost,
    endPeriodPost,
    setStartPeriodPostId,
    setEndPeriodPostId,
    resetPeriod,
    createPeriod
  }
}
