import { computed, ref } from 'vue'
import { IPost } from 'src/types'
import useLifelogPresets from 'src/composables/client/Lifelog/useLifelogPreset'

interface PostAction {
  name: string
  label: string
  icon: string
  fn: () => void
}

export function useLifeLogPostActions(post: IPost) {
  const showEditPostModal = ref(false)

  const {
    startPresetPostId,
    endPresetPostId,
    setStartPresetPostId,
    setEndPresetPostId
  } = useLifelogPresets()

  const isPostStartPreset = computed(() => startPresetPostId.value === post.id)
  const isPostEndPreset = computed(() => endPresetPostId.value === post.id)

  const userInitial = computed(() => post.user.name.substring(0, 1).toUpperCase())

  const actions: PostAction[] = [
    {
      name: 'edit_post',
      label: 'Редактировать',
      icon: 'edit',
      fn: () => {
        showEditPostModal.value = true
      }
    },
    {
      name: 'start_preset',
      label: 'Начало диапазона preset',
      icon: 'flag',
      fn: () => setStartPresetPostId(post.id)
    },
    {
      name: 'end_preset',
      label: 'Конец диапазона preset',
      icon: 'outlined_flag',
      fn: () => setEndPresetPostId(post.id)
    }
  ]

  return {
    showEditPostModal,
    isPostStartPreset,
    isPostEndPreset,
    userInitial,
    actions
  }
}
