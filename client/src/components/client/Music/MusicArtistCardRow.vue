<template>
  <q-card class="artist-card" flat bordered>
    <q-card-section horizontal>
      <q-img
        class="artist-card__image"
        :src="artist.image"
        :alt="artist.name + ' image'"
      />
      <q-card-section class="flex column justify-between">
        <div class="text-h6 q-mb-sm">
          <router-link :to="`/music/artists/${artist.id}/albums`" class="artist-card__link">{{ artist.name }}</router-link>
        </div>
        <div class="q-gutter-xs">
          <q-chip
            v-for="tag in artist.relationships.tags.data"
            :key="tag.id"
            size="md"
            color="primary"
            text-color="white"
            outline
          >
            {{ tag.name }}
          </q-chip>
        </div>
      </q-card-section>
    </q-card-section>
  </q-card>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import { IArtist } from 'src/components/client/Music/types'

const props = defineProps<{
  artist: IArtist
}>()
const artist = ref(props.artist)
</script>
<style lang="scss" scoped>
.artist-card {

  &__image {
    min-width: 150px;
    width: 150px;
    height: 150px;
  }

  &__link {
    text-decoration: none;
    color: #000;

    &:hover {
      color: #ccc;
    }
  }
}
</style>
