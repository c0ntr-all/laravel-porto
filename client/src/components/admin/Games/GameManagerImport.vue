<template>
  <div>
    <div class="q-mb-md">
      <div class="text-h6">Import</div>
      <div class="text-caption text-grey-7">
        Укажите идентификатор игры в Steam. Название, описание, обложка, дата выхода, разработчик и издатель заполнятся автоматически.
      </div>
    </div>

    <q-card class="q-mb-lg" flat bordered>
      <q-card-section>
        <q-form class="row items-end q-col-gutter-md" @submit.prevent="submit">
          <div class="col">
            <q-input
              v-model="steamInput"
              ref="steamInputRef"
              label="Steam ID"
              hint="Например: 1737870 или https://store.steampowered.com/app/1737870"
              outlined
              dense
              lazy-rules
              :disable="store.isImporting"
              :rules="[validateInput]"
            >
              <template #prepend>
                <q-icon name="sports_esports" />
              </template>
            </q-input>
          </div>
          <div class="col-auto">
            <q-btn
              type="submit"
              icon="cloud_download"
              label="Import"
              color="primary"
              unelevated
              no-caps
              :loading="store.isImporting"
              :disable="!canSubmit"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>

    <div class="text-h6 q-mb-md">Import history</div>

    <q-card flat bordered>
      <q-inner-loading :showing="store.isLoading">
        <q-spinner color="primary" size="2em" />
      </q-inner-loading>

      <q-list v-if="store.imports.length" separator>
        <q-item
          v-for="item in store.imports"
          :key="item.id"
          class="import-row"
        >
          <q-item-section avatar class="import-row__cover-wrap">
            <div class="import-row__cover">
              <q-img
                v-if="item.game?.img"
                :src="item.game.img"
                :alt="item.game.name"
                fit="cover"
              >
                <template #error>
                  <div class="import-row__cover-fallback">
                    <q-icon name="sports_esports" />
                  </div>
                </template>
              </q-img>
              <div v-else class="import-row__cover-fallback">
                <q-icon name="sports_esports" />
              </div>
            </div>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-subtitle1 text-weight-medium">
              {{ item.game?.name || `Steam ${item.steam_id}` }}
            </q-item-label>
            <q-item-label caption>
              Steam ID {{ item.steam_id }}
              <span v-if="airDate(item)"> · {{ airDate(item) }}</span>
              <span v-if="durationLabel(item)"> · {{ durationLabel(item) }}</span>
            </q-item-label>
            <q-item-label
              v-if="item.error_message"
              caption
              class="text-negative"
            >
              {{ item.error_message }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <div class="column items-end q-gutter-xs">
              <q-chip
                dense
                :color="statusColor(item.status)"
                text-color="white"
              >
                {{ GAME_IMPORT_STATUS_LABELS[item.status] }}
              </q-chip>
              <q-chip
                v-if="item.status === GameImportStatusEnum.COMPLETED && item.was_created != null"
                dense
                outline
                color="primary"
              >
                {{ item.was_created ? 'Created' : 'Updated' }}
              </q-chip>
              <q-btn
                v-if="steamLink(item)"
                dense
                flat
                no-caps
                size="sm"
                icon="open_in_new"
                label="Steam"
                :href="steamLink(item) ?? undefined"
                target="_blank"
                rel="noopener noreferrer"
              />
            </div>
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="!store.isLoading" class="q-pa-lg text-grey-6">
        No imports yet. Paste a Steam ID to start.
      </div>

      <div v-if="store.hasMoreImports" ref="sentinel" class="list-sentinel" />
      <div v-if="store.isLoadingMore" class="flex justify-center q-py-md">
        <q-spinner color="primary" />
      </div>
    </q-card>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import type { QInput } from 'quasar'
import { useGameImportStore } from 'src/stores/modules/gameImportStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import { formatGameAirDate, parseSteamId, steamStoreUrl } from 'src/utils/game'
import {
  GameImportStatusEnum,
  GAME_IMPORT_STATUS_LABELS
} from 'src/enums/Game/GameImportStatusEnum'
import { IGameImport } from 'src/types/Game'

const store = useGameImportStore()
const steamInput = ref('')
const steamInputRef = ref<QInput | null>(null)

const canSubmit = computed(() => (
  Boolean(parseSteamId(steamInput.value)) && !store.isImporting
))

function validateInput(value: string): true | string {
  return parseSteamId(value) ? true : 'Укажите Steam ID или ссылку на игру в магазине'
}

function airDate(item: IGameImport): string {
  return formatGameAirDate(item.game?.air_date)
}

function statusColor(status: GameImportStatusEnum): string {
  if (status === GameImportStatusEnum.COMPLETED) {
    return 'positive'
  }

  if (status === GameImportStatusEnum.FAILED) {
    return 'negative'
  }

  return 'grey-7'
}

function durationLabel(item: IGameImport): string {
  if (item.duration_ms == null) {
    return ''
  }

  const seconds = item.duration_ms / 1000

  return seconds < 10 ? `${seconds.toFixed(1)}s` : `${Math.round(seconds)}s`
}

function steamLink(item: IGameImport): string | null {
  return steamStoreUrl(item.steam_id)
}

async function submit(): Promise<void> {
  const isValid = await steamInputRef.value?.validate()
  const steamId = parseSteamId(steamInput.value)

  if (!isValid || !steamId) {
    return
  }

  const imported = await store.importFromSteam(steamId)

  if (imported?.status === GameImportStatusEnum.COMPLETED) {
    steamInput.value = ''
    steamInputRef.value?.resetValidation()
  }
}

const { sentinel } = useScrollSentinel(
  () => { void store.getImports({ append: true }) },
  () => store.hasMoreImports && !store.isLoading && !store.isLoadingMore
)

onMounted(() => {
  if (!store.imports.length) {
    void store.getImports()
  }
})
</script>

<style lang="scss" scoped>
.import-row {
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
}

.list-sentinel {
  height: 1px;
}
</style>
