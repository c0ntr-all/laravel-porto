<template>
  <router-link
    class="movie-franchise-card"
    :to="{ name: 'movie-franchise', params: { id: franchise.id } }"
  >
    <div class="movie-franchise-card__media">
      <q-img
        v-if="franchiseImageUrl(franchise)"
        :src="franchiseImageUrl(franchise) ?? ''"
        :alt="franchise.name"
        fit="cover"
      >
        <template #error>
          <div class="movie-franchise-card__fallback">
            <q-icon name="collections_bookmark" size="36px" />
          </div>
        </template>
      </q-img>
      <div v-else class="movie-franchise-card__fallback">
        <q-icon name="collections_bookmark" size="36px" />
      </div>
    </div>
    <div class="movie-franchise-card__title">{{ franchise.name }}</div>
  </router-link>
</template>

<script lang="ts" setup>
import { franchiseImageUrl } from 'src/api/mappers/Movie/movie.mapper'
import { IMovieFranchise } from 'src/types/Movie'

defineProps<{
  franchise: IMovieFranchise
}>()
</script>

<style lang="scss" scoped>
.movie-franchise-card {
  display: block;
  color: inherit;
  text-decoration: none;

  &__media {
    overflow: hidden;
    aspect-ratio: 2 / 3;
    border-radius: 16px;
    background:
      linear-gradient(135deg, rgba(108, 95, 252, 0.14), rgba(38, 166, 154, 0.08));
    box-shadow: 0 8px 20px rgba(40, 47, 83, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;

    :deep(.q-img) {
      height: 100%;
    }
  }

  &:hover &__media {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(40, 47, 83, 0.14);
  }

  &__fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
  }

  &__title {
    margin-top: 10px;
    font-size: 15px;
    font-weight: 600;
    line-height: 1.35;
    color: #282f53;
  }
}
</style>
