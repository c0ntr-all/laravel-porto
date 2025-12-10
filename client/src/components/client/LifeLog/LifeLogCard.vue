<template>
  <div class="ll-card-wrap">
    <q-avatar
      class="ll-card-wrap__avatar"
      color="primary"
      text-color="white"
    >
      {{ userAvatar }}
      <q-tooltip>
        {{ post.user.email }}
      </q-tooltip>
    </q-avatar>
    <q-card class="ll-card bg-grey-2" flat bordered>
      <q-card-section class="q-pa-sm">
        <div class="row items-center no-wrap">
          <div class="col">
            <div class="text-h6">{{ post.title }}</div>
          </div>
          <div class="col-auto">
            <q-btn color="grey-7" round flat icon="more_vert">
              <q-menu cover auto-close>
                <q-list>
                  <q-item
                    v-for="action in availableActions"
                    :key="action.name"
                    @click="action.fn"
                    clickable
                  >
                    <q-item-section>
                      <div class="flex items-center">
                        <q-icon
                          size="xs"
                          :name="action.icon"
                          flat
                          round
                          dense
                        />
                        <div class="q-ml-xs">{{ action.label }}</div>
                      </div>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
          </div>
        </div>
      </q-card-section>
      <q-card-section v-if="post.attachments?.length" class="q-pa-sm">
        <div class="row q-col-gutter-xs">
          <div class="col-2 flex column justify-end"
               v-for="attachment in post.attachments"
               :key="attachment.id"
               @click="openCarousel(attachment.id)"
          >
            <LifeLogCardImage
              :image="attachment"
            />
          </div>
        </div>
        <GalleryCarousel
          v-model="showCarousel"
          v-model:current-slide-id="currentSlideId"
          :slides="post.attachments"
        />
      </q-card-section>
      <q-card-section class="q-pa-sm">
        <div class="row items-center no-wrap">
          <div class="col">
            <div class="text-subtitle2">Дата события: {{ post.date }} <span v-if="post.time">{{ post.time }}</span></div>
          </div>
        </div>
      </q-card-section>
      <q-card-section class="q-pa-sm">
        <div v-if="post.content" v-html="post.content"></div>
      </q-card-section>
      <q-card-section class="q-pa-sm">
        <template v-if="post.tags.length">
          <LifeLogTag
            v-for="tag in post.tags"
            :key="tag.id"
            :tag="tag"
            :color="'primary'"
            :text-color="'white'"
            dense
          />
        </template>
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
  padding-left: 3.5rem;

  &__avatar {
    position: absolute;
    left: 0;
  }
}
.ll-card {
  width: 100%;
}
</style>
