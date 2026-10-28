<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card class="game-form-dialog">
      <q-form @submit.prevent="submit">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Редактирование</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-gutter-md">
          <q-input
            v-model="model.name"
            label="Название"
            filled
            dense
            :rules="[validateName]"
          />
          <q-input
            v-model="model.description"
            label="Описание"
            type="textarea"
            filled
            dense
            autogrow
            hint="Необязательно, до 20000 символов"
            :rules="[validateDescription]"
          />
          <q-input
            v-model="model.img"
            label="Изображение"
            filled
            dense
            hint="Необязательный URL обложки"
            :rules="[validateImg]"
          />
          <q-input
            v-model="model.air_date"
            label="Дата выхода"
            type="date"
            filled
            dense
            :rules="[validateDate]"
          />
          <div class="row q-col-gutter-md">
            <div class="col-12 col-sm-6">
              <q-input
                v-model="model.developer"
                label="Разработчик"
                filled
                dense
                :rules="[validateParty]"
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-input
                v-model="model.publisher"
                label="Издатель"
                filled
                dense
                :rules="[validateParty]"
              />
            </div>
          </div>
          <q-input
            v-model="model.steam_id"
            label="Steam ID"
            filled
            dense
            hint="Необязательно. Нужен для ссылки на Steam и реимпорта"
            :rules="[validateSteamId]"
          />
        </q-card-section>

        <q-card-section align="right">
          <q-btn
            type="submit"
            label="Сохранить"
            color="primary"
            unelevated
            no-caps
            :loading="gameStore.isSaving"
          />
        </q-card-section>
      </q-form>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { reactive, watch } from 'vue'
import { useGameAdminStore } from 'src/stores/modules/gameAdminStore'
import { IGame, IGameWriteDto } from 'src/types/Game'
import { parseSteamId } from 'src/utils/game'

const props = defineProps<{
  modelValue: boolean
  game?: IGame | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const gameStore = useGameAdminStore()

const model = reactive({
  name: '',
  description: '',
  img: '',
  air_date: '',
  developer: '',
  publisher: '',
  steam_id: ''
})

function validateName(value: string | null): true | string {
  const name = String(value ?? '').trim()

  if (!name) {
    return 'Укажите название'
  }

  if (name.length > 255) {
    return 'Не длиннее 255 символов'
  }

  return true
}

function validateDescription(value: string | null): true | string {
  if (String(value ?? '').length > 20000) {
    return 'Не длиннее 20000 символов'
  }

  return true
}

function validateParty(value: string | null): true | string {
  if (String(value ?? '').trim().length > 255) {
    return 'Не длиннее 255 символов'
  }

  return true
}

function validateImg(value: string | null): true | string {
  const trimmed = String(value ?? '').trim()

  if (!trimmed) {
    return true
  }

  if (trimmed.length > 4096) {
    return 'Не длиннее 4096 символов'
  }

  try {
    const url = new URL(trimmed)

    if (url.protocol !== 'http:' && url.protocol !== 'https:') {
      return 'Нужна ссылка http или https'
    }

    return true
  } catch {
    return 'Нужна корректная ссылка'
  }
}

function validateDate(value: string | null): true | string {
  const trimmed = String(value ?? '').trim()

  if (!trimmed) {
    return true
  }

  if (!/^\d{4}-\d{2}-\d{2}$/.test(trimmed) || Number.isNaN(new Date(`${trimmed}T00:00:00`).getTime())) {
    return 'Укажите дату'
  }

  return true
}

function validateSteamId(value: string | null): true | string {
  const trimmed = String(value ?? '').trim()

  if (!trimmed) {
    return true
  }

  return parseSteamId(trimmed) ? true : 'Steam ID — целое число больше 0'
}

function payload(): IGameWriteDto {
  return {
    name: model.name.trim(),
    description: model.description.trim() || null,
    img: model.img.trim() || null,
    air_date: model.air_date.trim() || null,
    developer: model.developer.trim() || null,
    publisher: model.publisher.trim() || null,
    steam_id: parseSteamId(model.steam_id)
  }
}

async function submit(): Promise<void> {
  if (!props.game) {
    return
  }

  const saved = await gameStore.updateGame(props.game.id, payload())

  if (saved) {
    emit('update:modelValue', false)
  }
}

watch(() => [props.modelValue, props.game] as const, ([open]) => {
  if (!open) {
    return
  }

  model.name = props.game?.name ?? ''
  model.description = props.game?.description ?? ''
  model.img = props.game?.img ?? ''
  model.air_date = props.game?.air_date?.slice(0, 10) ?? ''
  model.developer = props.game?.developer ?? ''
  model.publisher = props.game?.publisher ?? ''
  model.steam_id = props.game?.steam_id ? String(props.game.steam_id) : ''
})
</script>

<style lang="scss" scoped>
.game-form-dialog {
  min-width: 480px;
  width: 100%;
  max-width: 640px;
}

@media (max-width: 600px) {
  .game-form-dialog {
    min-width: 0;
  }
}
</style>
