<template>
  <q-layout view="lHh Lpr lFf">
    <q-header class="header q-py-sm flex items-center">
      <q-toolbar>
        <q-btn
          @click="toggleLeftDrawer"
          icon="menu"
          aria-label="Menu"
          color="primary"
          flat
          dense
          round
        />

        <AppMusicPlayer />

        <q-space />

        <q-btn class="q-ml-md" icon="dark_mode" color="primary" flat dense />

        <AppNotificationsBell />

        <AppUserMenu />
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" :width="250" :breakpoint="600" show-if-above>
      <q-scroll-area style="height: calc(100% - 75px); margin-top: 75px; border-right: 1px solid #ddd">
        <q-list padding>
          <q-item
            v-for="item in menuItems"
            :key="item.path"
            :index="item.path"
            :to="item.path"
            class="sidebar-menu__item"
            :exact="item.meta?.exact !== false && !item.children?.length"
            clickable
            v-ripple
          >
            <q-item-section side>
              <q-icon :name="item.meta?.icon ?? defaultIcon" color="primary" />
            </q-item-section>
            <q-item-section>{{ item.meta?.title || defaultTitleText }}</q-item-section>
          </q-item>

          <q-separator color="grey-3" />

          <q-item
            v-for="item in adminItems"
            :key="item.path"
            :index="item.path"
            :to="item.path"
            class="sidebar-menu__item"
            exact
            clickable
            v-ripple
          >
            <q-item-section side>
              <q-icon :name="item.meta?.icon ?? 'label'" color="primary" />
            </q-item-section>
            <q-item-section>{{ item.meta?.title || defaultTitleText }}</q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>

      <div class="logo-wrap q-px-md absolute-top">
        <q-img class="logo" src="logo/logo-1.svg" fit="contain" />
      </div>
    </q-drawer>

    <q-page-container>
      <q-page class="q-pa-lg">
        <div class="text-h4 q-mb-lg">{{ $route.meta?.title || defaultTitleText }}</div>
        <router-view />
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from 'src/stores/modules/userStore'
// import { useNotificationsRealtime } from 'src/composables/useNotificationsRealtime'
import AppMusicPlayer from 'src/components/default/AppMusicPlayer.vue'
import AppUserMenu from 'src/components/default/AppUserMenu.vue'
import AppNotificationsBell from 'src/components/default/AppNotificationsBell.vue'

defineOptions({
  name: 'MainLayout'
})

interface Route {
  path: string
  children?: unknown[]
  meta?: {
    icon?: string
    title?: string
    menu?: boolean
    is_admin?: boolean
    exact?: boolean
  }
}

const $router = useRouter()
const $route: Route = useRoute()
const userStore = useUserStore()
const leftDrawerOpen = ref<boolean>(false)

// useNotificationsRealtime()

const defaultTitleText = 'No title for route'
const defaultIcon = 'label'

const menuItems = computed((): Route[] => {
  const routes = $router.options.routes
  if (routes.length > 0 && routes[0].children) {
    return routes[0].children.filter((route: Route) => route.meta?.menu === true)
  }
  return []
})

const adminItems = computed((): Route[] => {
  const routes = $router.options.routes
  if (routes.length > 0 && routes[0].children) {
    return routes[0].children.filter((route: Route) => route.meta?.is_admin === true)
  }
  return []
})

const toggleLeftDrawer = (): void => {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

onMounted(() => {
  if (userStore.isLoggedIn) {
    void userStore.fetchCurrentUser().catch(() => undefined)
  }
})
</script>
<style lang="scss" scoped>
.q-page {
  background-color: #f0f0f5;
}
.header {
  height: 75px;
  background-color: #ffffff;
  border-block-end: 1px solid #e9edf4;

  &-image {
    height: 100%;
    z-index: -1;
    opacity: .2;
    filter: grayscale(100%);
  }
}
.logo-wrap {
  height: 75px;
  border-block-end: 1px solid #e9edf4;
  border-inline-end: 1px solid #e9edf4;
}
.logo {
  height: 75px;
}
.sidebar-menu {
  &__item {
    color: #282f53;
    font-size: 16px;
  }
}
.q-router-link--active {
  color: $secondary;
}
</style>
