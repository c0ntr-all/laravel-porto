<template>
  <div
    class="ll-card-wrap"
    :class="{
      'll-card-wrap--start-preset': isPostStartPreset,
      'll-card-wrap--end-preset': isPostEndPreset
    }
  ">
    <AppUserAvatar
      class="ll-card-wrap__avatar"
      :user="post.user"
    >
      <q-tooltip>
        {{ post.user.email }}
      </q-tooltip>
    </AppUserAvatar>
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
              v-if="attachment.attachment_type === 'gallery_images'"
              :image="attachment"
            />
            <LifeLogCardVideo
              v-else-if="attachment.attachment_type === 'gallery_videos'"
              :image="attachment"
            />
            <div v-else>
              Identification attachment error
            </div>
          </div>
        </div>
        <GalleryCarousel
          v-model="showCarousel"
          v-model:current-slide-id="currentSlideId"
          :slides="post.attachments"
        />
      </q-card-section>
      <q-card-section class="q-pa-sm">
        <div v-if="post.content" v-html="post.content"></div>
      </q-card-section>
      <q-card-section class="flex items-center q-pa-sm">
        <div class="ll-card-tags">
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
        </div>
        <div class="ll-card-datetime">
          <div class="text-subtitle2">{{ post.date }} <span v-if="post.time">{{ post.time }}</span></div>
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
import useLifelogPresets from 'src/composables/client/Lifelog/useLifelogPreset'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'

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

const { startPresetPostId, endPresetPostId, setStartPresetPostId, setEndPresetPostId } = useLifelogPresets()

const showEditPostModal = ref<boolean>(false)
const showDeletePostModal = ref<boolean>(false)
const showCarousel = ref<boolean>(false)
const currentSlideId = ref<string>('')

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
}, {
  fn: () => {
    setStartPresetPostId(props.post.id)
  },
  label: 'Start preset',
  name: 'start_preset',
  icon: 'line_axis'
}, {
  fn: () => {
    setEndPresetPostId(props.post.id)
  },
  label: 'End preset',
  name: 'end_preset',
  icon: 'line_axis'
}]

const isPostStartPreset = computed(() => startPresetPostId.value === props.post.id)
const isPostEndPreset = computed(() => endPresetPostId.value === props.post.id)
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

  &--start-preset {
    &:before {
      content: '';
      position: absolute;
      width: calc(100% - 3.5rem);
      height: 100%;
      background: #000;
      z-index: 1;
      opacity: .5;
    }

    &:after {
      content: "start";
      position: absolute;
      margin-top: -30px;
      margin-left: -10px;
      left: 50%;
      top: 50%;
      font-size: 2rem;
      color: #fff;
      z-index: 2;
    }
  }

  &--end-preset {
    &:before {
      content: '';
      position: absolute;
      width: calc(100% - 3.5rem);
      height: 100%;
      background: #000;
      z-index: 1;
      opacity: .5;
    }

    &:after {
      content: 'end';
      position: absolute;
      margin-top: -30px;
      margin-left: -10px;
      left: 50%;
      top: 50%;
      font-size: 2rem;
      color: #fff;
      z-index: 2;
    }
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
