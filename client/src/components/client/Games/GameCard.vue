<template>
  <router-link
    class="game-card"
    :to="{ name: 'game', params: { id: game.id } }"
  >
    <div class="game-card__cover">
      <q-img
        v-if="game.img"
        :src="game.img"
        :alt="game.name"
        class="game-card__image"
        fit="cover"
      >
        <template #error>
          <div class="game-card__placeholder">
            <q-icon name="sports_esports" size="42px" />
          </div>
        </template>
      </q-img>
      <div v-else class="game-card__placeholder">
        <q-icon name="sports_esports" size="42px" />
      </div>
    </div>

    <div class="game-card__title" :title="game.name">{{ game.name }}</div>
  </router-link>
</template>

<script lang="ts" setup>
import { IGame } from 'src/types/Game'

defineProps<{
  game: IGame
}>()
</script>

<style lang="scss" scoped>
.game-card {
  display: block;
  min-width: 0;
  color: inherit;
  text-decoration: none;

  &:hover {
    .game-card__cover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(40, 47, 83, 0.16);
    }

    .game-card__image {
      transform: scale(1.04);
    }

    .game-card__title {
      color: $primary;
    }
  }

  &__cover {
    position: relative;
    overflow: hidden;
    aspect-ratio: 460 / 215;
    border-radius: 16px;
    background: #ececf4;
    box-shadow: 0 6px 16px rgba(40, 47, 83, 0.08);
    margin-bottom: 0.75rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  &__image {
    height: 100%;
    transition: transform 0.35s ease;

    :deep(.q-img__image) {
      transition: transform 0.35s ease;
    }
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
    background:
      linear-gradient(135deg, rgba(108, 95, 252, 0.12), rgba(38, 166, 154, 0.08));
  }

  &__title {
    font-size: 15px;
    font-weight: 600;
    line-height: 1.35;
    color: #282f53;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    overflow: hidden;
    transition: color 0.15s ease;
  }
}
</style>
