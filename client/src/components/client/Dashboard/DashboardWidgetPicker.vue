<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card class="picker" style="min-width: 520px; max-width: 720px">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Добавить виджет</div>
        <q-space />
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>

      <q-card-section>
        <q-input v-model="query" dense outlined placeholder="Поиск виджета" class="q-mb-md">
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>

        <div v-for="group in groups" :key="group.category" class="picker__group">
          <div class="picker__label">{{ group.label }}</div>
          <div class="picker__grid">
            <q-card
              v-for="item in group.items"
              :key="item.type"
              class="picker__item"
              flat
              bordered
              clickable
              @click="emit('select', item)"
            >
              <q-card-section>
                <div class="picker__item-head">
                  <q-icon :name="item.icon" color="primary" />
                  <span>{{ item.name }}</span>
                </div>
                <div class="picker__item-text">{{ item.description }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { IWidgetType } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  modelValue: boolean
  catalog: IWidgetType[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  select: [item: IWidgetType]
}>()

const query = ref('')

const groups = computed(() => {
  const needle = query.value.trim().toLowerCase()
  const filtered = props.catalog.filter(item => {
    if (!needle) {
      return true
    }

    return [item.name, item.description, item.type, item.category_label]
      .join(' ')
      .toLowerCase()
      .includes(needle)
  })

  const map = new Map<string, { category: string; label: string; items: IWidgetType[] }>()
  for (const item of filtered) {
    const current = map.get(item.category) ?? {
      category: item.category,
      label: item.category_label,
      items: []
    }
    current.items.push(item)
    map.set(item.category, current)
  }

  return [...map.values()]
})
</script>

<style lang="scss" scoped>
.picker {
  border-radius: 16px;

  &__group + &__group {
    margin-top: 16px;
  }

  &__label {
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #777a8f;
  }

  &__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }

  &__item {
    border-radius: 12px;
  }

  &__item-head {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #282f53;
  }

  &__item-text {
    margin-top: 6px;
    font-size: 12px;
    color: #777a8f;
    line-height: 1.4;
  }
}
</style>
