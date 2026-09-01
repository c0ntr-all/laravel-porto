<template>
  <div class="media-widget">
    <router-link
      v-for="item in items"
      :key="item.id"
      class="media-widget__item"
      :to="item.href || '#'"
    >
      <q-img :src="item.image || ''" ratio="1" fit="cover" class="media-widget__image">
        <div class="absolute-bottom media-widget__caption">{{ item.title }}</div>
      </q-img>
    </router-link>

    <div v-if="!items.length" class="media-widget__empty">Пока нет медиа</div>
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
.media-widget {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
  gap: 8px;

  &__item {
    border-radius: 10px;
    overflow: hidden;
  }

  &__caption {
    padding: 4px 6px;
    font-size: 11px;
    color: #fff;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.55));
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__empty {
    grid-column: 1 / -1;
    padding: 16px 4px;
    color: #9aa0b5;
    font-size: 13px;
  }
}
</style>
