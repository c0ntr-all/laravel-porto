<template>
  <div class="row lifelog-container">
    <div class="lifelog-post-form-wrap q-mb-md">
      <LifeLogPostForm />
    </div>
    <LifeLogCard
      v-for="post in posts"
      :key="post.id"
      :post="post"
    />
  </div>
</template>

<script lang="ts" setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePostStore } from 'src/stores/modules/LifeLog/postStore'
import LifeLogPostForm from 'src/components/client/LifeLog/LifeLogPostForm.vue'
import LifeLogCard from 'src/components/client/LifeLog/LifeLogCard.vue'

const postStore = usePostStore()
const { posts } = storeToRefs(postStore)

onMounted(() => {
  postStore.getPosts()
})
</script>

<style lang="scss" scoped>
.lifelog-container {
  max-width: 700px;

  .lifelog-post-form-wrap {
    width: 100%;
  }
}
</style>
