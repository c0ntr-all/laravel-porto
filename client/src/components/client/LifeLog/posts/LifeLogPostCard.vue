<template>
  <article
    class="ll-post-card"
    :class="{
      'll-post-card--start-preset': isPostStartPreset,
      'll-post-card--end-preset': isPostEndPreset
    }"
  >
    <header class="ll-post-card__header row items-start no-wrap">
      <AppUserAvatar
        class="ll-post-card__avatar"
        :user="post.user"
        size="40px"
      >
        <q-tooltip>{{ post.user.email }}</q-tooltip>
      </AppUserAvatar>

      <div class="col q-pl-sm">
        <div class="row items-start no-wrap">
          <div class="col">
            <h3 class="ll-post-card__title">{{ post.title }}</h3>
          </div>
          <q-btn
            flat
            round
            dense
            color="grey-7"
            icon="more_vert"
          >
            <q-menu cover auto-close>
              <q-list dense>
                <q-item
                  v-for="action in actions"
                  :key="action.name"
                  clickable
                  @click="action.fn"
                >
                  <q-item-section avatar>
                    <q-icon :name="action.icon" size="xs" />
                  </q-item-section>
                  <q-item-section>{{ action.label }}</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </div>
    </header>

    <section
      v-if="post.attachments?.length"
      class="ll-post-card__section"
    >
      <LifeLogPostAttachments :post="post" />
    </section>

    <section
      v-if="post.content"
      class="ll-post-card__section ll-post-card__content"
      v-html="post.content"
    />

    <footer class="ll-post-card__section ll-post-card__footer">
      <LifeLogPostMeta :post="post" />
    </footer>

    <q-dialog v-model="showEditPostModal">
      <PostFormUpdate :post="post" />
    </q-dialog>
  </article>
</template>

<script setup lang="ts">
import { IPost } from 'src/types'
import { useLifeLogPostActions } from 'src/composables/client/Lifelog/useLifeLogPostActions'
import LifeLogPostAttachments from 'src/components/client/LifeLog/posts/LifeLogPostAttachments.vue'
import LifeLogPostMeta from 'src/components/client/LifeLog/posts/LifeLogPostMeta.vue'
import PostFormUpdate from 'src/components/client/LifeLog/forms/PostFormUpdate.vue'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'

const props = defineProps<{
  post: IPost
}>()

const {
  showEditPostModal,
  isPostStartPreset,
  isPostEndPreset,
  actions
} = useLifeLogPostActions(props.post)
</script>

<style scoped lang="scss">
.ll-post-card {
  position: relative;
  background: #fff;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);

  &__header {
    padding: 14px 14px 0;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    line-height: 1.35;
    font-weight: 600;
  }

  &__section {
    padding: 12px 14px 0;
  }

  &__content {
    color: #334155;
    line-height: 1.55;
    word-break: break-word;
  }

  &__footer {
    padding-bottom: 14px;
    margin-top: 4px;
  }

  &--start-preset,
  &--end-preset {
    outline: 2px solid var(--q-primary);
    outline-offset: 0;
  }

  &--start-preset::after,
  &--end-preset::after {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    background: var(--q-primary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  &--start-preset::after {
    content: 'start';
  }

  &--end-preset::after {
    content: 'end';
  }
}
</style>
