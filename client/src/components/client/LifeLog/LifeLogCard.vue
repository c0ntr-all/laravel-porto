<template>
  <q-card class="ll-card bg-grey-2" flat bordered>
    <q-card-section>
      <div class="row items-center no-wrap">
        <div class="col">
          <div class="text-h6">{{ post.title }}</div>
          <div class="text-subtitle2">by {{ post.user.email }}</div>
        </div>
      </div>
    </q-card-section>
    <q-card-section>
      {{ post.content }}
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { IPost } from 'src/types/LifeLog/post'

const props = defineProps<{
  post: IPost
}>()
const post = ref(props.post)
</script>

<style scoped lang="scss">
.ll-card {
  width: 100%;
}
</style>
