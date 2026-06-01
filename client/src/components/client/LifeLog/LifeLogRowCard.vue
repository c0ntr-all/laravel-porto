<template>
  <div class="ll-card-wrap">
    <q-avatar
      class="ll-card-wrap__avatar"
      color="primary"
      text-color="white"
      size="md"
    >
      {{ userAvatar }}
      <q-tooltip>
        {{ post.user.email }}
      </q-tooltip>
    </q-avatar>
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
import { computed, ref, toRefs } from 'vue'
import { IPost } from 'src/types/LifeLog/post'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'
import PostFormUpdate from 'src/components/client/LifeLog/forms/PostFormUpdate.vue'
import LifeLogCardImage from 'src/components/client/LifeLog/LifeLogCardImage.vue'
import GalleryCarousel from 'src/components/client/Gallery/GalleryCarousel.vue'
import LifeLogCardVideo from 'src/components/client/LifeLog/forms/LifeLogCardVideo.vue'

interface Action {
  fn: () => void
  name: string
  label: string
  icon: string
}

const props = defineProps<{
  post: IPost
}>()
const { post } = toRefs(props)
const showEditPostModal = ref<boolean>(false)
const showDeletePostModal = ref<boolean>(false)
const showCarousel = ref<boolean>(false)
const currentSlideId = ref<string>('')

const userAvatar = computed(() => post.value.user.name.substring(0, 1))

const availableActions: Action[] = [{
  fn: () => {
    showEditPostModal.value = true
  },
  label: 'Edit Post',
  name: 'edit_post',
  icon: 'edit'
}, {
  fn: () => {
    showDeletePostModal.value = true
  },
  label: 'Delete Post',
  name: 'delete_post',
  icon: 'delete'
}]
const openCarousel = (id: string) => {
  currentSlideId.value = id
  showCarousel.value = true
}
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
