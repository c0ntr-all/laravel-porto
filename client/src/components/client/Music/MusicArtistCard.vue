<template>
  <router-link :to="`/music/artists/${artist.id}/albums`" class="artist-card-link">
    <q-card class="artist-card" flat bordered>
      <q-img
        :src="artist.image"
        :alt="artist.name"
        ratio="1"
        class="artist-card__image"
      >
        <div class="absolute-bottom artist-card__overlay">
          <div class="artist-card__name ellipsis">{{ artist.name }}</div>
        </div>
      </q-img>
      <q-card-section v-if="visibleTags.length" class="q-pa-sm">
        <q-chip
          v-for="tag in visibleTags"
          :key="tag.id"
          size="sm"
          color="primary"
          text-color="white"
          outline
          dense
        >
          {{ tag.name }}
        </q-chip>
        <q-chip
          v-if="hiddenTagsCount"
          size="sm"
          color="grey-4"
          text-color="grey-8"
          dense
        >
          +{{ hiddenTagsCount }}
        </q-chip>
      </q-card-section>
    </q-card>
  </router-link>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IArtist } from 'src/types'

const props = defineProps<{
  artist: IArtist
}>()

const visibleTags = computed(() => (props.artist.tags ?? []).slice(0, 3))
const hiddenTagsCount = computed(() => Math.max((props.artist.tags ?? []).length - 3, 0))
</script>

<style lang="scss" scoped>
.artist-card-link {
  display: block;
  text-decoration: none;
  color: inherit;
}

.artist-card {
  overflow: hidden;

  &__overlay {
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.72));
    padding: 12px;
  }

  &__name {
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
  }
}
</style>
