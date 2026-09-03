<template>
  <q-btn
    class="q-ml-md"
    icon="notifications"
    color="primary"
    flat
    dense
    round
    aria-label="Оповещения"
  >
    <q-badge
      v-if="badgeLabel"
      color="negative"
      floating
      rounded
    >
      {{ badgeLabel }}
    </q-badge>

    <q-menu
      v-model="menuOpen"
      class="notifications-menu"
      anchor="bottom right"
      self="top right"
      :offset="[0, 12]"
      @show="onShow"
      @hide="onHide"
    >
      <div class="notifications-menu__header">
        <div class="notifications-menu__title">Оповещения</div>
        <q-btn
          v-close-popup
          flat
          dense
          no-caps
          color="primary"
          label="Все"
          :to="{ name: 'notifications' }"
        />
      </div>

      <q-separator />

      <div class="notifications-menu__body">
        <div
          v-if="isInboxLoading"
          class="notifications-menu__loading"
        >
          Загрузка…
        </div>
        <div
          v-if="inbox.length === 0"
          class="notifications-menu__state"
        >
          Нет непрочитанных оповещений
        </div>
        <NotificationListItem
          v-for="item in inbox"
          :key="item.id"
          :notification="item"
          compact
          :preview-length="120"
          @select="onSelect"
        />
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useNotificationStore } from 'src/stores/modules/notificationStore'
import NotificationListItem from 'src/components/client/Notifications/NotificationListItem.vue'

const $router = useRouter()
const notificationStore = useNotificationStore()
const { inbox, isInboxLoading, badgeLabel } = storeToRefs(notificationStore)
const menuOpen = ref(false)

const onShow = (): void => {
  void notificationStore.openInbox()
}

const onHide = (): void => {
  notificationStore.closeInbox()
}

const onSelect = (id: string): void => {
  menuOpen.value = false
  notificationStore.setHighlightedId(id)
  void $router.push({
    name: 'notifications',
    query: { highlight: id }
  })
}
</script>

<style lang="scss" scoped>
.notifications-menu {
  width: 380px;
  max-width: calc(100vw - 24px);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 16px 40px rgba(40, 47, 83, 0.16);

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 12px 12px 16px;
  }

  &__title {
    font-size: 15px;
    font-weight: 600;
    color: #282f53;
  }

  &__body {
    position: relative;
    min-height: 88px;
    max-height: 420px;
    overflow: auto;
    padding: 8px;
  }

  &__loading {
    position: absolute;
    inset: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.86);
    color: #8b8ea3;
    font-size: 13px;
  }

  &__state {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 72px;
    padding: 24px 12px;
    color: #8b8ea3;
    font-size: 13px;
    text-align: center;
  }
}
</style>
