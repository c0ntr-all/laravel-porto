<template>
  <q-avatar
    :size="size"
    color="primary"
    text-color="white"
    :class="avatarClass"
  >
    <img
      v-if="src && !failed"
      :src="src"
      alt=""
      @error="failed = true"
    >
    <span v-else>{{ initials }}</span>
    <slot />
  </q-avatar>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { IUser } from 'src/types/user'
import { getUserInitials } from 'src/api/mappers/user.mapper'

const props = withDefaults(defineProps<{
  user: IUser
  size?: string
  avatarClass?: string | Record<string, boolean>
}>(), {
  size: '40px'
})

const failed = ref(false)
const src = computed(() => props.user.avatar || '')
const initials = computed(() => getUserInitials(props.user))

watch(src, () => {
  failed.value = false
})
</script>
