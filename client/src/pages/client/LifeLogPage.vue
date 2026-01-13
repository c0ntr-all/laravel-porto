<template>
  <div class="row lifelog-container">
    <div class="lifelog-post-form-wrap q-mb-md">
      <PostFormCreate />
    </div>
    <div class="lifelog-filter-wrap q-mb-md">
      <LifeLogPostsFilter
        @submit="onFilterSubmit"
        @reset="onFilterReset"
      />
    </div>
    <div class="lifelog-posts-wrap q-mb-md q-gutter-sm">
      <div v-if="postStore.isLoading">loading...</div>
      <template v-else>
        <template v-if="postsCount">
          <LifeLogCard
            v-for="post in posts"
            :key="post.id"
            :post="post"
          />
        </template>
        <AppNoResultsPlug
          v-else
          title="There are no posts yet."
          body="Try to create!"
        />
      </template>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { isEmpty } from 'radash'
import { usePostStore } from 'src/stores/modules/postStore'
import { ITag } from 'src/types/tag'
import { ITagsFilterData } from 'src/types'
import PostFormCreate from 'src/components/client/LifeLog/forms/PostFormCreate.vue'
import LifeLogPostsFilter from 'src/components/client/LifeLog/LifeLogPostsFilter.vue'
import LifeLogCard from 'src/components/client/LifeLog/LifeLogCard.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const postStore = usePostStore()
const { posts, postsCount } = storeToRefs(postStore)

const onFilterSubmit = (tagsFilterData: ITagsFilterData) => {
  reloadPosts(tagsFilterData)
}
const onFilterReset = () => {
  reloadPosts()
}

const reloadPosts = (tagsFilterData: ITagsFilterData | object = {}) => {
  let filterData = {}

  // Type guard для проверки наличия свойства tags
  const hasTags = (data: any): data is ITagsFilterData => {
    return 'tags' in data && Array.isArray(data.tags)
  }

  if (!isEmpty(tagsFilterData) && hasTags(tagsFilterData)) {
    filterData = {
      ...tagsFilterData,
      tags: tagsFilterData.tags.map((tag: ITag) => tag.id)
    }
  }
  postStore.getPosts(filterData)
}

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
  .lifelog-posts-wrap {
    width: 100%;
  }
  .lifelog-filter-wrap {
    width: 100%;
  }
}
</style>
