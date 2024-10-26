<template>
  <q-card class="content-container q-mb-md" flat>
    <q-card-section class="content-container__section">
      <p>
        <q-skeleton type="text" :width="'150px'" />
      </p>
      <p>
        <q-skeleton type="text" :width="'350px'" />
        <q-skeleton type="text" :width="'350px'" />
        <q-skeleton type="text" :width="'350px'" />
        <q-skeleton type="text" :width="'350px'" />
      </p>
    </q-card-section>
    <q-card-section>
      <p>
        <q-skeleton type="text" :width="'100px'" />
      </p>
      <div class="row q-gutter-md">
        <GalleryMediaCardSkeleton
          v-for="q in Math.floor(Math.random() * 30)"
          :key="q"
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import GalleryMediaCardSkeleton from 'src/components/client/Gallery/GalleryMediaCardSkeleton.vue'
</script>

<style lang="scss" scoped>

</style>
