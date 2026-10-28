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
              :disable="gameStore.isImporting"
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
              :loading="gameStore.isImporting"
              :disable="!canSubmit"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>

    <q-card v-if="imported" flat bordered>
      <q-item>
        <q-item-section avatar class="imported-game__cover-wrap">
          <div class="imported-game__cover">
            <q-img
              v-if="imported.img"
              :src="imported.img"
              :alt="imported.name"
              fit="cover"
            />
            <div v-else class="imported-game__cover-fallback">
              <q-icon name="sports_esports" />
            </div>
          </div>
        </q-item-section>
        <q-item-section>
          <q-item-label class="text-subtitle1 text-weight-medium">
            {{ imported.name }}
          </q-item-label>
          <q-item-label caption>
            Steam {{ imported.steam_id }}
          </q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-btn
            dense
            flat
            no-caps
            color="primary"
            icon="open_in_new"
            label="Открыть"
            :to="{ name: 'game', params: { id: imported.id } }"
          />
        </q-item-section>
      </q-item>
    </q-card>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import type { QInput } from 'quasar'
import { useGameAdminStore } from 'src/stores/modules/gameAdminStore'
import { parseSteamId } from 'src/utils/game'
import { IGame } from 'src/types/Game'

const gameStore = useGameAdminStore()
const steamInput = ref('')
const steamInputRef = ref<QInput | null>(null)
const imported = ref<IGame | null>(null)

const canSubmit = computed(() => (
  Boolean(parseSteamId(steamInput.value)) && !gameStore.isImporting
))

function validateInput(value: string): true | string {
  return parseSteamId(value) ? true : 'Укажите Steam ID или ссылку на игру в магазине'
}

async function submit(): Promise<void> {
  const isValid = await steamInputRef.value?.validate()
  const steamId = parseSteamId(steamInput.value)

  if (!isValid || !steamId) {
    return
  }

  const result = await gameStore.importFromSteam(steamId)

  if (result) {
    imported.value = result
    steamInput.value = ''
    steamInputRef.value?.resetValidation()
  }
}
</script>

<style lang="scss" scoped>
.imported-game {
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
</style>
