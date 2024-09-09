<template>
  <q-card class="q-mb-md" flat>
    <q-card-section>
      <div class="artist-albums q-mb-lg">
        <div class="text-h5 q-mb-sm">
          <q-skeleton type="text" width="200px"/>
        </div>
        <div class="row items-start q-gutter-md">
          <AlbumCardSkeleton
            v-for="item in [1,2,3,4,5]"
            :key="item"
          />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import AlbumCardSkeleton from 'src/components/client/Music/MusicAlbumCardSkeleton.vue'
</script>

<style lang="scss" scoped>
</style>
