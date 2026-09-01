<template>
  <q-btn class="user-menu-trigger q-ml-md" round flat>
    <AppUserAvatar :user="user" size="36px" />

    <q-menu
      class="user-menu"
      anchor="bottom right"
      self="top right"
      :offset="[0, 12]"
    >
      <div class="user-menu__header">
        <AppUserAvatar :user="user" size="52px" />
        <div class="user-menu__identity">
          <div class="user-menu__name">{{ displayName }}</div>
          <div class="user-menu__login">{{ displayLogin }}</div>
          <q-badge class="user-menu__role" outline color="primary">
            {{ displayRole }}
          </q-badge>
        </div>
      </div>

      <q-separator />

      <q-list class="user-menu__list" padding>
        <q-item
          v-close-popup
          :to="{ name: 'profile' }"
          class="user-menu__item"
          clickable
          v-ripple
        >
          <q-item-section avatar>
            <div class="user-menu__icon user-menu__icon--profile">
              <q-icon name="person" />
            </div>
          </q-item-section>
          <q-item-section>
            <q-item-label>Профиль</q-item-label>
            <q-item-label caption>Личные данные</q-item-label>
          </q-item-section>
        </q-item>

        <q-item
          v-close-popup
          :to="{ name: 'settings-dashboard' }"
          class="user-menu__item"
          clickable
          v-ripple
        >
          <q-item-section avatar>
            <div class="user-menu__icon user-menu__icon--settings">
              <q-icon name="settings" />
            </div>
          </q-item-section>
          <q-item-section>
            <q-item-label>Настройки</q-item-label>
            <q-item-label caption>Разделы приложения</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>

      <q-separator />

      <q-list class="user-menu__list">
        <q-item
          v-close-popup
          class="user-menu__item user-menu__item--logout"
          clickable
          v-ripple
          @click="onLogout"
        >
          <q-item-section avatar>
            <div class="user-menu__icon user-menu__icon--logout">
              <q-icon name="logout" />
            </div>
          </q-item-section>
          <q-item-section>Выйти</q-item-section>
        </q-item>
      </q-list>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useUserStore } from 'src/stores/modules/userStore'
import AppUserAvatar from 'src/components/default/AppUserAvatar.vue'

const $router = useRouter()
const userStore = useUserStore()
const { user, displayName, displayRole, displayLogin } = storeToRefs(userStore)

const onLogout = (): void => {
  userStore.logout().finally(() => {
    $router.push('/login')
  })
}
</script>

<style lang="scss" scoped>
.user-menu {
  width: 300px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 16px 40px rgba(40, 47, 83, 0.16);

  &__header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: linear-gradient(180deg, rgba(108, 95, 252, 0.08), transparent);
  }

  &__identity {
    min-width: 0;
  }

  &__name {
    font-size: 15px;
    font-weight: 600;
    color: #282f53;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__login {
    margin: 2px 0 6px;
    font-size: 12px;
    color: #777a8f;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__role {
    border-radius: 999px;
  }

  &__list {
    padding: 6px;
  }

  &__item {
    border-radius: 12px;
    min-height: 52px;
  }

  &__item--logout {
    color: $negative;
  }

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    color: $primary;
    background: $primary-light;

    &--settings {
      color: #1f7a72;
      background: rgba(38, 166, 154, 0.14);
    }

    &--logout {
      color: $negative;
      background: rgba(232, 38, 70, 0.1);
    }
  }
}
</style>
