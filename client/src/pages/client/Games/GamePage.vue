<template>
  <div class="q-mb-md">
    <AppBackButton link="/games" text="К играм" />
  </div>

  <div v-if="gameStore.isGameLoading && !game" class="game-page-skeleton">
    <q-skeleton class="game-page-skeleton__cover" square />
    <div class="game-page-skeleton__info">
      <q-skeleton type="text" width="60%" />
      <q-skeleton type="text" width="40%" />
      <q-skeleton type="text" width="50%" />
      <q-skeleton type="text" width="90%" />
      <q-skeleton type="text" width="80%" />
    </div>
  </div>

  <div v-else-if="game" class="game-page">
    <div class="game-page__cover">
      <q-img
        v-if="game.img"
        :src="game.img"
        :alt="game.name"
        class="game-page__image"
        fit="cover"
      >
        <template #error>
          <div class="game-page__placeholder">
            <q-icon name="sports_esports" size="48px" />
          </div>
        </template>
      </q-img>
      <div v-else class="game-page__placeholder">
        <q-icon name="sports_esports" size="48px" />
      </div>
    </div>

    <div class="game-page__main">
      <h1 class="game-page__title">{{ game.name }}</h1>

      <dl class="game-page__facts">
        <div class="game-page__fact">
          <dt>Дата выхода</dt>
          <dd>{{ airDate || 'Не указана' }}</dd>
        </div>
        <div class="game-page__fact">
          <dt>Разработчик</dt>
          <dd>{{ game.developer || 'Не указан' }}</dd>
        </div>
        <div class="game-page__fact">
          <dt>Издатель</dt>
          <dd>{{ game.publisher || 'Не указан' }}</dd>
        </div>
      </dl>

      <q-btn
        v-if="steamUrl"
        class="game-page__steam"
        unelevated
        no-caps
        color="primary"
        icon="open_in_new"
        label="Перейти в steam"
        :href="steamUrl"
        target="_blank"
        rel="noopener noreferrer"
      />

      <div class="game-page__description">
        <p v-if="game.description">{{ game.description }}</p>
        <p v-else class="text-grey-5">Описание отсутствует</p>
      </div>
    </div>
  </div>

  <q-card v-else class="q-mb-md" flat>
    <AppNoResultsPlug
      title="Игра не найдена"
      body="Запись могла быть удалена или недоступна."
    />
  </q-card>
</template>

<script lang="ts" setup>
import { computed, watch } from 'vue'
import { useGameStore } from 'src/stores/modules/gameStore'
import { formatGameAirDate, steamStoreUrl } from 'src/utils/game'
import AppBackButton from 'src/components/default/AppBackButton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  id: string
}>()

const gameStore = useGameStore()
const game = computed(() => (
  gameStore.game?.id === props.id ? gameStore.game : null
))
const airDate = computed(() => formatGameAirDate(game.value?.air_date))
const steamUrl = computed(() => (
  game.value?.steam_id ? steamStoreUrl(game.value.steam_id) : null
))

watch(
  () => props.id,
  (id) => {
    void gameStore.getGame(id)
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.game-page-skeleton,
.game-page {
  display: grid;
  grid-template-columns: minmax(240px, 460px) minmax(0, 1fr);
  gap: 2rem;
  align-items: start;
}

.game-page-skeleton__cover,
.game-page__cover {
  width: 100%;
  aspect-ratio: 460 / 215;
  border-radius: 16px;
  overflow: hidden;
  background: #ececf4;
}

.game-page {
  &__image,
  &__placeholder {
    height: 100%;
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9aa0b8;
    background:
      linear-gradient(135deg, rgba(108, 95, 252, 0.12), rgba(38, 166, 154, 0.08));
  }

  &__title {
    margin: 0 0 1rem;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__facts {
    display: grid;
    gap: 0.75rem;
    margin: 0 0 1.25rem;
  }

  &__fact {
    display: grid;
    grid-template-columns: 140px minmax(0, 1fr);
    gap: 12px;

    dt {
      margin: 0;
      color: #777a8f;
    }

    dd {
      margin: 0;
      color: #282f53;
    }
  }

  &__steam {
    margin-bottom: 1.25rem;
  }

  &__description {
    margin: 0;
    color: #282f53;
    font-size: 15px;
    line-height: 1.55;
    white-space: pre-line;

    p {
      margin: 0;
    }
  }
}

@media (max-width: 800px) {
  .game-page-skeleton,
  .game-page {
    grid-template-columns: 1fr;
  }

  .game-page__fact {
    grid-template-columns: 1fr;
    gap: 2px;
  }
}
</style>
