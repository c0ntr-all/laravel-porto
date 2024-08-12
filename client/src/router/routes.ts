import { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [{
      path: '/dashboard',
      component: () => import('pages/client/DashboardPage.vue'),
      meta: {
        title: 'Dashboard',
        icon: 'home',
        menu: true
      },
      name: 'dashboard',
      alias: '/'
    }]
  }, {
    path: '/login',
    component: () => import('layouts/Login.vue'),
    meta: {
      title: 'Authorization',
      menu: false
    },
    name: 'login',
    alias: '/login'
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
