<template>
  <header class="games-page__head">
    <h1 class="games-page__title">Все игры</h1>
    <p v-if="totalLabel" class="games-page__total">
      {{ totalLabel }}
    </p>
    <p v-else-if="gameStore.isGamesLoading" class="games-page__total games-page__total--muted">
      Загрузка каталога…
    </p>
  </header>

  <div class="games-toolbar">
    <q-input
      v-model="searchText"
      class="games-toolbar__search"
      placeholder="Название"
      outlined
      dense
      debounce="400"
      clearable
      @update:model-value="onSearch"
    >
      <template #prepend>
        <q-icon name="search" />
      </template>
    </q-input>

    <q-btn-toggle
      v-model="viewMode"
      unelevated
      dense
      toggle-color="primary"
      color="grey-2"
      text-color="primary"
      :options="viewModeOptions"
    >
      <template #tile>
        <q-icon name="grid_view" size="sm" />
        <q-tooltip>Блоки</q-tooltip>
      </template>
      <template #list>
        <q-icon name="view_agenda" size="sm" />
        <q-tooltip>Список</q-tooltip>
      </template>
    </q-btn-toggle>
  </div>

  <div
    v-if="gameStore.isGamesLoading && !gameStore.games.length"
    :class="isList ? 'games-list' : 'games-grid'"
  >
    <q-skeleton
      v-for="item in 8"
      :key="item"
      class="games-skeleton"
      :class="{ 'games-skeleton--row': isList }"
      square
    />
  </div>

  <template v-else-if="gameStore.games.length">
    <div v-if="!isList" class="games-grid">
      <GameCard
        v-for="item in gameStore.games"
        :key="item.id"
        :game="item"
      />
    </div>
    <div v-else class="games-list">
      <GameCardRow
        v-for="item in gameStore.games"
        :key="item.id"
        :game="item"
      />
    </div>
  </template>

  <q-card v-else class="q-mb-md" flat>
    <AppNoResultsPlug
      :title="emptyTitle"
      :body="emptyBody"
    />
  </q-card>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { useGameStore } from 'src/stores/modules/gameStore'
import { GamesViewModeEnum } from 'src/enums/Game/GamesViewModeEnum'
import { useGamesViewMode } from 'src/composables/client/Games/useGamesViewMode'
import { formatGamesCount } from 'src/utils/game'
import GameCard from 'src/components/client/Games/GameCard.vue'
import GameCardRow from 'src/components/client/Games/GameCardRow.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const gameStore = useGameStore()
const searchText = ref(gameStore.listName)
const { viewMode } = useGamesViewMode()

const viewModeOptions = [
  { value: GamesViewModeEnum.TILE, slot: 'tile' },
  { value: GamesViewModeEnum.LIST, slot: 'list' }
]

const isList = computed(() => viewMode.value === GamesViewModeEnum.LIST)

const totalLabel = computed(() => {
  if (gameStore.gamesTotal == null) {
    return ''
  }

  return `${formatGamesCount(gameStore.gamesTotal)} в каталоге`
})

const emptyTitle = computed(() => (
  searchText.value.trim() ? 'Ничего не найдено' : 'Игр пока нет'
))

const emptyBody = computed(() => (
  searchText.value.trim()
    ? 'Попробуйте другое название.'
    : 'Импортируйте игру в Games Manager.'
))

function onSearch(value: string | number | null): void {
  void gameStore.getGames({
    name: value == null ? '' : String(value)
  })
}

onMounted(() => {
  void gameStore.getGames({ name: searchText.value })
})
</script>

<style lang="scss" scoped>
.games-page {
  &__head {
    margin-bottom: 1.25rem;
  }

  &__title {
    margin: 0 0 4px;
    font-size: 2rem;
    line-height: 1.2;
    font-weight: 700;
    color: #282f53;
  }

  &__total {
    margin: 0;
    color: #777a8f;
    font-size: 15px;
    line-height: 1.4;

    &--muted {
      opacity: 0.75;
    }
  }
}

.games-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 1.25rem;

  &__search {
    flex: 1 1 260px;
    max-width: 420px;
  }
}

.games-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.5rem 1.25rem;
}

.games-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.games-skeleton {
  aspect-ratio: 460 / 215;
  border-radius: 16px;

  &--row {
    aspect-ratio: auto;
    height: 92px;
  }
}
</style>
