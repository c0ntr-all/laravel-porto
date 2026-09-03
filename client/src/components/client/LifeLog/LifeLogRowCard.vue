<template>
  <div class="ll-card-wrap">
    <AppUserAvatar
      class="ll-card-wrap__avatar"
      :user="post.user"
      size="md"
    >
      <q-tooltip>
        {{ post.user.email }}
      </q-tooltip>
    </AppUserAvatar>
    <q-card class="ll-card bg-grey-2" flat bordered dense>
      <q-card-section class="q-pa-xs">
        <div class="row items-center no-wrap">
          <div class="col">
            <div>{{ post.title }}</div>
          </div>
          <div class="col-auto q-pl-xs">
            <div class="text-subtitle2">{{ post.date }} <span v-if="post.time">{{ post.time }}</span></div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <q-dialog v-model="showEditPostModal">
      <PostFormUpdate :post="post" />
    </q-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, toRefs } from 'vue'
import { IPost } from 'src/types/LifeLog/post'
import PostFormUpdate from 'src/components/client/LifeLog/forms/PostFormUpdate.vue'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'

const props = defineProps<{
  post: IPost
}>()
const { post } = toRefs(props)
const showEditPostModal = ref<boolean>(false)
</script>

<style scoped lang="scss">
.ll-card-wrap {
  position: relative;
  padding-left: 2.5rem;

  &__avatar {
    position: absolute;
    left: 0;
  }
}
.ll-card {
  width: 100%;

  &-tags {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-wrap: wrap;
  }

  &-datetime {
    flex-shrink: 0;
  }
}
</style>
