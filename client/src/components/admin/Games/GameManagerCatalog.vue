<template>
  <div>
    <div class="row items-end q-col-gutter-sm q-mb-md">
      <div class="col-12 col-md">
        <div class="text-h6">Catalog</div>
        <div class="text-caption text-grey-7">
          Игры каталога. Редактирование, реимпорт из Steam и удаление.
        </div>
      </div>
      <div class="col-12 col-sm-4 col-md-3">
        <q-input
          v-model="searchText"
          label="Название"
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
      </div>
    </div>

    <q-card flat bordered>
      <q-inner-loading :showing="gameStore.isGamesLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="gameStore.games.length" separator>
        <q-item
          v-for="item in gameStore.games"
          :key="item.id"
          class="game-row"
        >
          <q-item-section avatar class="game-row__cover-wrap">
            <div class="game-row__cover">
              <q-img
                v-if="item.img"
                :src="item.img"
                :alt="item.name"
                fit="cover"
              >
                <template #error>
                  <div class="game-row__cover-fallback">
                    <q-icon name="sports_esports" />
                  </div>
                </template>
              </q-img>
              <div v-else class="game-row__cover-fallback">
                <q-icon name="sports_esports" />
              </div>
            </div>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-subtitle1 text-weight-medium">
              {{ item.name }}
            </q-item-label>
            <q-item-label caption>
              <span v-if="item.developer">{{ item.developer }}</span>
              <span v-if="item.publisher"> · {{ item.publisher }}</span>
              <span v-if="airDate(item)"> · {{ airDate(item) }}</span>
              <span v-if="item.steam_id"> · Steam {{ item.steam_id }}</span>
            </q-item-label>
          </q-item-section>
          <q-item-section side top>
            <div class="game-row__actions">
              <q-btn
                label="Редактирование"
                color="primary"
                unelevated
                dense
                no-caps
                @click="openEdit(item)"
              />
              <q-btn
                label="Реимпорт"
                color="primary"
                outline
                dense
                no-caps
                :disable="!item.steam_id || importStore.isImporting"
                @click="openReimport(item)"
              />
              <q-btn
                label="Удаление"
                color="negative"
                outline
                dense
                no-caps
                :disable="gameStore.isSaving"
                @click="openDelete(item)"
              />
            </div>
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="!gameStore.isGamesLoading" class="q-pa-lg text-grey-6">
        {{ emptyText }}
      </div>
    </q-card>

    <GameFormDialog
      v-model="showEdit"
      :game="editing"
    />

    <q-dialog v-model="showReimport">
      <q-card class="game-dialog">
        <q-card-section class="text-h6">Реимпорт</q-card-section>
        <q-card-section>
          Обновить «{{ reimporting?.name }}» из Steam
          <span v-if="reimporting?.steam_id"> ({{ reimporting.steam_id }})</span>?
          Текущие название, описание, обложка, дата выхода, разработчик и издатель будут заменены данными магазина.
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps v-close-popup :disable="importStore.isImporting">Отмена</q-btn>
          <q-btn
            color="primary"
            unelevated
            no-caps
            label="Реимпорт"
            :loading="importStore.isImporting"
            @click="runReimport"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="showDelete">
      <q-card class="game-dialog">
        <q-card-section class="text-h6">Удаление</q-card-section>
        <q-card-section>
          Удалить «{{ deleting?.name }}»?
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps v-close-popup :disable="gameStore.isSaving">Отмена</q-btn>
          <q-btn
            color="negative"
            unelevated
            no-caps
            label="Удаление"
            :loading="gameStore.isSaving"
            @click="runDelete"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { useGameAdminStore } from 'src/stores/modules/gameAdminStore'
import { useGameImportStore } from 'src/stores/modules/gameImportStore'
import { formatGameAirDate } from 'src/utils/game'
import { GameImportStatusEnum } from 'src/enums/Game/GameImportStatusEnum'
import { IGame } from 'src/types/Game'
import GameFormDialog from 'src/components/admin/Games/GameFormDialog.vue'

const gameStore = useGameAdminStore()
const importStore = useGameImportStore()
const searchText = ref(gameStore.listName)
const showEdit = ref(false)
const editing = ref<IGame | null>(null)
const showReimport = ref(false)
const reimporting = ref<IGame | null>(null)
const showDelete = ref(false)
const deleting = ref<IGame | null>(null)

const emptyText = computed(() => (
  searchText.value.trim()
    ? 'По этому названию игр нет.'
    : 'Игр пока нет. Импортируйте игру по Steam ID на вкладке Import.'
))

function airDate(item: IGame): string {
  return formatGameAirDate(item.air_date)
}

function onSearch(value: string | number | null): void {
  void gameStore.getGames({
    name: value == null ? '' : String(value)
  })
}

function openEdit(item: IGame): void {
  editing.value = item
  showEdit.value = true
}

function openReimport(item: IGame): void {
  if (!item.steam_id || importStore.isImporting) {
    return
  }

  reimporting.value = item
  showReimport.value = true
}

function openDelete(item: IGame): void {
  deleting.value = item
  showDelete.value = true
}

async function runReimport(): Promise<void> {
  const steamId = reimporting.value?.steam_id

  if (!steamId) {
    return
  }

  const imported = await importStore.importFromSteam(steamId)

  if (imported?.status === GameImportStatusEnum.COMPLETED) {
    showReimport.value = false
    reimporting.value = null
  }
}

async function runDelete(): Promise<void> {
  if (!deleting.value) {
    return
  }

  const ok = await gameStore.deleteGame(deleting.value.id)

  if (ok) {
    showDelete.value = false
    deleting.value = null
  }
}

onMounted(() => {
  void gameStore.getGames({ name: searchText.value })
})
</script>

<style lang="scss" scoped>
.game-row {
  min-height: 96px;

  &__cover-wrap {
    min-width: 128px;
  }

  &__cover {
    width: 112px;
    height: 52px;
    overflow: hidden;
    border-radius: 8px;
    background: rgba(40, 47, 83, 0.06);

    :deep(.q-img) {
      height: 100%;
    }
  }

  &__cover-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #9aa0b8;
  }

  &__actions {
    display: flex;
    flex-direction: column;
    gap: 6px;

    .q-btn {
      min-width: 148px;
    }
  }
}

.game-dialog {
  min-width: 360px;
  max-width: 480px;
}

@media (max-width: 700px) {
  .game-row__actions {
    .q-btn {
      min-width: 0;
    }
  }

  .game-dialog {
    min-width: 0;
  }
}
</style>
