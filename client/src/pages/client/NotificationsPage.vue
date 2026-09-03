<template>
  <div class="notifications-page">
    <q-card class="notifications-page__card" flat>
      <q-card-section class="notifications-page__toolbar">
        <div>
          <div class="notifications-page__caption">
            Все входящие сообщения. Непрочитанные отмечены цветом.
          </div>
        </div>
        <div class="notifications-page__meta">
          Всего: {{ meta.total }}
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div v-if="isListLoading" class="notifications-page__state">
          <q-spinner color="primary" size="28px" />
        </div>

        <div v-else-if="items.length === 0" class="notifications-page__state notifications-page__state--empty">
          Пока нет оповещений
        </div>

        <div v-else class="notifications-page__list">
          <NotificationListItem
            v-for="item in items"
            :key="item.id"
            :notification="item"
            :flashing="item.id === flashingId"
            @select="onSelect"
          />
        </div>
      </q-card-section>

      <q-card-section v-if="meta.last_page > 1" class="notifications-page__pager">
        <q-pagination
          :model-value="currentPage"
          :max="meta.last_page"
          :max-pages="7"
          direction-links
          boundary-links
          color="primary"
          @update:model-value="onPageChange"
        />
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import { useNotificationStore } from 'src/stores/modules/notificationStore'
import NotificationListItem from 'src/components/client/Notifications/NotificationListItem.vue'
import { NOTIFICATION_PAGE_SIZE } from 'src/constants/notifications'

const $route = useRoute()
const $router = useRouter()
const notificationStore = useNotificationStore()
const { items, meta, isListLoading, highlightedId } = storeToRefs(notificationStore)

const flashingId = ref<string | null>(null)
let flashTimer: ReturnType<typeof setTimeout> | null = null

const currentPage = computed(() => {
  const raw = Number($route.query.page)
  return Number.isInteger(raw) && raw > 0 ? raw : 1
})

const highlightFromQuery = computed(() => {
  const value = $route.query.highlight
  return typeof value === 'string' && value ? value : null
})

function clearFlash(): void {
  if (flashTimer) {
    clearTimeout(flashTimer)
    flashTimer = null
  }
  flashingId.value = null
}

async function flashNotification(id: string): Promise<void> {
  await nextTick()
  const target = document.getElementById(`notification-${id}`)
  target?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  clearFlash()
  flashingId.value = id
  flashTimer = setTimeout(() => {
    flashingId.value = null
    flashTimer = null
  }, 1600)
}

async function loadPage(page: number): Promise<void> {
  await notificationStore.fetchPage(page, NOTIFICATION_PAGE_SIZE)
  const highlight = highlightFromQuery.value || highlightedId.value
  if (highlight && items.value.some(item => item.id === highlight)) {
    await flashNotification(highlight)
    notificationStore.setHighlightedId(null)
  }
}

function onPageChange(page: number): void {
  void $router.replace({
    name: 'notifications',
    query: {
      ...$route.query,
      page: page > 1 ? String(page) : undefined
    }
  })
}

function onSelect(id: string): void {
  void flashNotification(id)
}

watch(
  [currentPage, highlightFromQuery],
  ([page]) => {
    void loadPage(page)
  },
  { immediate: true }
)

onBeforeUnmount(() => {
  clearFlash()
})
</script>

<style lang="scss" scoped>
.notifications-page {
  max-width: 760px;

  &__card {
    border-radius: 18px;
    overflow: hidden;
  }

  &__toolbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
  }

  &__caption {
    color: #777a8f;
    font-size: 14px;
  }

  &__meta {
    color: #8b8ea3;
    font-size: 13px;
    white-space: nowrap;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__state {
    display: flex;
    justify-content: center;
    padding: 48px 16px;
    color: #8b8ea3;

    &--empty {
      font-size: 14px;
    }
  }

  &__pager {
    display: flex;
    justify-content: center;
  }
}
</style>
