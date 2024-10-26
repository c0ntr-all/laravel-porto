<template>
  <q-card class="media-card col-2">
    <q-skeleton class="media-card__image" height="200px" square />
  </q-card>
</template>

<script lang="ts" setup>
</script>

<style lang="scss" scoped>
.media-card {
  &__image {
    max-height: 176px;
  }
}
</style>
