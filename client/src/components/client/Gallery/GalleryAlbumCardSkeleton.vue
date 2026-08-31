<template>
  <div class="album-card-skeleton">
    <div class="album-card-skeleton__cover-wrap">
      <q-skeleton class="album-card-skeleton__cover" square />
    </div>
    <q-skeleton type="text" width="70%" />
    <q-skeleton type="text" width="45%" />
  </div>
</template>

<script lang="ts" setup>
</script>

<style lang="scss" scoped>
.album-card-skeleton {
  min-width: 0;

  &__cover-wrap {
    margin-bottom: 0.75rem;
  }

  &__cover {
    width: 100%;
    aspect-ratio: 4 / 3;
    border-radius: 16px;
  }
}
</style>
