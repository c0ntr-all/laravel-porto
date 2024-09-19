<template>
  <q-card class="playlist-card col-4">
    <q-skeleton height="200px" square />
  </q-card>
</template>

<script lang="ts" setup>
</script>

<style lang="scss" scoped>
.playlist-card {
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
