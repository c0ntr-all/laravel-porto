import { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/dashboard',
    name: 'main',
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
      alias: '/dashboard'
    }, {
      path: '/task-manager',
      component: () => import('pages/client/TaskManager/TaskManagerPage.vue'),
      meta: {
        title: 'Task Manager',
        icon: 'list',
        menu: true
      },
      name: 'task-manager',
      alias: '/task-manager'
    }]
  }, {
    path: '/login',
    component: () => import('layouts/LoginLayout.vue'),
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
