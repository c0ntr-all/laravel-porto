<template>
  <div class="list-widget">
    <router-link
      v-for="item in items"
      :key="item.id"
      class="list-widget__item"
      :to="item.href || '#'"
    >
      <q-img
        v-if="item.image"
        :src="item.image"
        class="list-widget__image"
        ratio="1"
        fit="cover"
      />
      <div class="list-widget__text">
        <div class="list-widget__title">{{ item.title }}</div>
        <div v-if="item.subtitle" class="list-widget__subtitle">{{ item.subtitle }}</div>
      </div>
      <q-icon name="chevron_right" color="grey-5" />
    </router-link>

    <div v-if="!items.length" class="list-widget__empty">Пока нет данных</div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IWidgetItem, IWidgetPayload } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  payload: IWidgetPayload | null
}>()

const items = computed<IWidgetItem[]>(() => {
  const raw = props.payload?.data?.items
  return Array.isArray(raw) ? raw : []
})
</script>

<style lang="scss" scoped>
.list-widget {
  display: flex;
  flex-direction: column;
  gap: 4px;

  &__item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 4px;
    border-radius: 10px;
    color: inherit;
    text-decoration: none;

    &:hover {
      background: rgba(108, 95, 252, 0.06);
    }
  }

  &__image {
    width: 36px;
    min-width: 36px;
    border-radius: 8px;
    overflow: hidden;
  }

  &__text {
    min-width: 0;
    flex: 1;
  }

  &__title {
    font-size: 14px;
    font-weight: 600;
    color: #282f53;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__subtitle {
    margin-top: 2px;
    font-size: 12px;
    color: #777a8f;
  }

  &__empty {
    padding: 16px 4px;
    color: #9aa0b5;
    font-size: 13px;
  }
}
</style>
