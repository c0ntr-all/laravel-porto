<template>
  <q-card class="ll-card" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-2'" flat bordered>
    <q-card-section>
      <div class="row items-center no-wrap">
        <div class="col">
          <div class="text-h6">{{ post.attributes.title }}</div>
          <div class="text-subtitle2">by {{ post.relationships.user.data.email }}</div>
        </div>
      </div>
    </q-card-section>
    <q-card-section>
      {{ post.attributes.content }}
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { IPost } from 'src/types/LifeLog/post'

const props = defineProps<{
  post: IPost[]
}>()
const post = ref(props.post)
</script>

<style scoped lang="scss">
.ll-card {
  width: 100%;
}
</style>
