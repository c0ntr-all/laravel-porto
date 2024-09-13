<template>
  <div class="playlist">
    <div class="playlist-head q-mb-lg">
      <div class="playlist-head__left">
        <div class="playlist-head__image q-mb-md">
          <q-skeleton height="200px" width="200px" square/>
        </div>
      </div>
      <div class="playlist-head__right">
        <h2 class="playlist-head__name">
          <q-skeleton type="text" width="300px"/>
        </h2>
        <div class="playlist-head__description">
          <q-skeleton type="text" width="300px"/>
          <q-skeleton type="text" width="300px"/>
          <q-skeleton type="text" width="300px"/>
          <q-skeleton type="text" width="300px"/>
        </div>
      </div>
    </div>
    <div class="playlist-body">
      <MusicTracksListSkeleton/>
    </div>
  </div>
</template>

<script lang="ts" setup>
import MusicTracksListSkeleton from 'src/components/client/Music/MusicTracksListSkeleton.vue'
</script>

<style lang="scss" scoped>
.playlist-head {
  display: flex;
  column-gap: 1rem;
  padding: 1rem 0 0 0;

  &__image {
    & > * {
      width: 200px;
      height: 200px;
    }
  }

  &__name {
    margin: 0 0 1rem 0;
    font-size: 45px;
    line-height: 45px;
    font-weight: 700;
  }

  &__description {
    margin: 0 0 1rem 0;

    &-item {
      margin-bottom: 1rem;
    }
  }
}

.playlist-body {
  background: #fff;
}
</style>
