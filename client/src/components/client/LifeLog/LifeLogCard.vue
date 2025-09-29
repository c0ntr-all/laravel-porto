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
            <div class="text-subtitle2">Дата события: {{ post.datetime }}</div>
          </div>
        </div>
      </q-card-section>
      <q-card-section class="q-pa-sm">
        {{ post.content }}
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
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { IPost } from 'src/types/LifeLog/post'
import LifeLogTag from 'src/components/client/LifeLog/LifeLogTag.vue'

const props = defineProps<{
  post: IPost
}>()
const post = ref(props.post)

const userAvatar = computed(() => post.value.user.name.substring(0, 1))
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
