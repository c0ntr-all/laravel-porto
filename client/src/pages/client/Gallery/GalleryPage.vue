<template>
  <q-card class="content-container q-mb-md" flat>
    <q-card-section class="content-container__section">
      <div class="row q-gutter-md">
        <q-card v-for="media in mediaList" :key="media.id" class="album-card col-2">
          <q-img :src="media.image" :alt="media.name" :height="'100%'">
            <div class="absolute-bottom text-h6">
              <router-link :to="`/gallery/albums/${media.id}`" class="album-card__link">
                {{ media.name }}
              </router-link>
            </div>
          </q-img>
        </q-card>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
const mediaList = [{
  id: 1,
  image: 'https://img.freepik.com/free-photo/3d-rendering-hydraulic-elements_23-2149333332.jpg',
  name: 'test'
}, {
  id: 2,
  image: 'https://img.freepik.com/free-photo/top-view-kettle-with-tea-with-cinnamon-fresh-black-tea-dark-background-color-table-morning-breakfast-coffee-photo-meal-egg-food_140725-157622.jpg',
  name: 'test 2'
}]
</script>

<style lang="scss" scoped>
.album-card {
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
