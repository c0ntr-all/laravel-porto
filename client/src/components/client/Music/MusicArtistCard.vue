<template>
  <q-card class="artist-card">
    <q-img :src="artist.image" :alt="artist.name + ' image'">
      <div class="absolute-bottom text-h6">
        <router-link :to="`/music/artists/${artist.id}/albums`" class="artist-card__link">{{ artist.name }}</router-link>
      </div>
    </q-img>
    <q-card-section class="q-pa-sm">
      <q-chip
        v-for="tag in artist.relationships.tags.data"
        :key="tag.id"
        size="sm"
        color="primary"
        text-color="white"
        outline
      >
        {{ tag.name }}
      </q-chip>
    </q-card-section>
  </q-card>
</template>
<script lang="ts" setup>
import { ref } from 'vue'

interface Tag {
  id: string
  name: string
}

interface Artist {
  id: string
  name: string
  image: string
  content: string
  relationships: {
    tags: {
      data: Tag[]
    }
  }
}

const props = defineProps<{
  artist: Artist
}>()
const artist = ref(props.artist)
</script>
<style lang="scss" scoped>
.artist-card {
  width: 100%;
  max-width: 250px;

  &__link {
    text-decoration: none;
    color: #fff;

    &:hover {
      color: #ccc;
    }
  }
}
</style>
