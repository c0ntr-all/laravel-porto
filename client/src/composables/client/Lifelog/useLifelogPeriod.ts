import { computed } from 'vue'
import { usePostStore } from 'src/stores/modules/postStore'
import { usePeriodStore } from 'src/stores/modules/periodStore'
import { IPeriodModel, IPost } from 'src/types'

export default function useLifelogPeriods() {
  const postStore = usePostStore()
  const periodStore = usePeriodStore()

  const startPeriodPostId = computed(() => periodStore.startPeriodPostId)
  const endPeriodPostId = computed(() => periodStore.endPeriodPostId)
  const startPeriodPost = computed<IPost>(() =>
    postStore.posts.find(post => post.id === startPeriodPostId.value)
  ) || null
  const endPeriodPost = computed<IPost>(() =>
    postStore.posts.find(post => post.id === endPeriodPostId.value)
  ) || null

  const setStartPeriodPostId = (postId: string): void => {
    periodStore.setStartPeriodPostId(postId)
  }
  const setEndPeriodPostId = (postId: string): void => {
    periodStore.setEndPeriodPostId(postId)
  }

  const resetPeriod = () => {
    periodStore.setStartPeriodPostId(null)
    periodStore.setEndPeriodPostId(null)
  }

  const createPeriod = (payload: IPeriodModel): Promise => {
    return periodStore.createPeriod(payload)
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
