import { computed } from 'vue'
import { usePostStore } from 'src/stores/modules/postStore'
import { usePresetStore } from 'src/stores/modules/presetStore'
import { IPreset, IPresetModel, IPost } from 'src/types'

export default function useLifelogPresets() {
  const postStore = usePostStore()
  const presetStore = usePresetStore()

  const startPresetPostId = computed(() => presetStore.startPresetPostId)
  const endPresetPostId = computed(() => presetStore.endPresetPostId)

  const startPresetPost = computed<IPost | undefined>(() =>
    postStore.posts.find(post => post.id === startPresetPostId.value)
  )

  const endPresetPost = computed<IPost | undefined>(() =>
    postStore.posts.find(post => post.id === endPresetPostId.value)
  )

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

  const createPreset = (payload: IPresetModel): Promise<IPreset> => {
    return presetStore.createPreset(payload)
  }

  const updatePreset = (id: string, payload: IPresetModel): Promise<IPreset> => {
    return presetStore.updatePreset(id, payload)
  }

  return {
    startPresetPostId,
    endPresetPostId,
    startPresetPost,
    endPresetPost,
    setStartPresetPostId,
    setEndPresetPostId,
    resetPreset,
    createPreset,
    updatePreset
  }
}
