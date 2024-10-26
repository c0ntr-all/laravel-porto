<template>
  <q-card class="content-container q-mb-md" flat>
    <q-card-section class="content-container__section">
      <div class="row q-gutter-md">
        <q-card
          v-for="q in Math.floor(Math.random() * 10)"
          :key="q"
          class="album-card col-2"
        >
          <q-skeleton :height="'189px'" square />
        </q-card>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
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
