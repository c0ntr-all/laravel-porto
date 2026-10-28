<template>
  <router-link
    class="game-card-row"
    :to="{ name: 'game', params: { id: game.id } }"
  >
    <div class="game-card-row__cover">
      <q-img
        v-if="game.img"
        :src="game.img"
        :alt="game.name"
        class="game-card-row__image"
        fit="cover"
      >
        <template #error>
          <div class="game-card-row__placeholder">
            <q-icon name="sports_esports" size="28px" />
          </div>
        </template>
      </q-img>
      <div v-else class="game-card-row__placeholder">
        <q-icon name="sports_esports" size="28px" />
      </div>
    </div>

    <div class="game-card-row__body">
      <div class="game-card-row__title" :title="game.name">{{ game.name }}</div>
      <div class="game-card-row__date">{{ airDate || 'Дата не указана' }}</div>
    </div>
  </router-link>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IGame } from 'src/types/Game'
import { formatGameAirDate } from 'src/utils/game'

const props = defineProps<{
  game: IGame
}>()

const airDate = computed(() => formatGameAirDate(props.game.air_date))
</script>

<style lang="scss" scoped>
.game-card-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 0;
  padding: 12px;
  border-radius: 16px;
  background: #fff;
  border: 1px solid rgba(40, 47, 83, 0.08);
  box-shadow: 0 6px 16px rgba(40, 47, 83, 0.06);
  color: inherit;
  text-decoration: none;
  transition: background-color 0.15s ease, border-color 0.15s ease;

  &:hover {
    background: rgba(242, 242, 242, 0.6);
    border-color: rgba(108, 95, 252, 0.28);

    .game-card-row__title {
      color: $primary;
    }
  }

  &__cover {
    flex: 0 0 168px;
    width: 168px;
    overflow: hidden;
    aspect-ratio: 460 / 215;
    border-radius: 12px;
    background: #ececf4;
  }

  &__image {
    height: 100%;
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

  &__body {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    min-width: 0;
    flex: 1;
  }

  &__title {
    font-size: 17px;
    font-weight: 600;
    line-height: 1.3;
    color: #282f53;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.15s ease;
  }

  &__date {
    font-size: 14px;
    line-height: 1.35;
    color: #777a8f;
  }
}

@media (max-width: 600px) {
  .game-card-row {
    &__cover {
      flex-basis: 120px;
      width: 120px;
    }

    &__title {
      font-size: 15px;
    }
  }
}
</style>
