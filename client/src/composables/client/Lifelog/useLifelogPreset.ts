import { computed } from 'vue'
import { usePostStore } from 'src/stores/modules/postStore'
import { usePresetStore } from 'src/stores/modules/presetStore'
import { IPresetModel, IPost } from 'src/types'

export default function useLifelogPresets() {
  const postStore = usePostStore()
  const presetStore = usePresetStore()

  const startPresetPostId = computed(() => presetStore.startPresetPostId)
  const endPresetPostId = computed(() => presetStore.endPresetPostId)
  const startPresetPost = computed<IPost>(() =>
    postStore.posts.find(post => post.id === startPresetPostId.value)
  ) || null
  const endPresetPost = computed<IPost>(() =>
    postStore.posts.find(post => post.id === endPresetPostId.value)
  ) || null

  const setStartPresetPostId = (postId: string): void => {
    presetStore.setStartPresetPostId(postId)
  }
  const setEndPresetPostId = (postId: string): void => {
    presetStore.setEndPresetPostId(postId)
  }

  const resetPreset = () => {
    presetStore.setStartPresetPostId(null)
    presetStore.setEndPresetPostId(null)
  }

  const createPreset = (payload: IPresetModel): Promise => {
    return presetStore.createPreset(payload)
  }

  return {
    startPresetPostId,
    endPresetPostId,
    startPresetPost,
    endPresetPost,
    setStartPresetPostId,
    setEndPresetPostId,
    resetPreset,
    createPreset
  }
}
