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
    }, {
      path: '/music',
      component: () => import('layouts/MusicLayout.vue'),
      meta: {
        title: 'Music',
        icon: 'music_note',
        menu: true
      },
      children: [{
        path: '/music',
        component: () => import('pages/client/Music/MusicPage.vue'),
        name: 'music',
        props: true
      }, {
        path: '/music/tags/:slug',
        component: () => import('pages/client/Music/TagPage.vue'),
        name: 'tag',
        props: true
      }, {
        path: '/music/artists/:id',
        component: () => import('pages/client/Music/ArtistPage.vue'),
        name: 'artist',
        redirect: (to) => {
          const { id } = to.params
          return `/music/artists/${id}/albums`
        },
        props: true,
        meta: {
          title: 'Музыка'
        },
        children: [{
          path: '/music/artists/:id/tracks',
          component: () => import('pages/client/Music/ArtistPage.vue'),
          name: 'artist-tracks',
          props: true
        }, {
          path: '/music/artists/:id/albums',
          component: () => import('pages/client/Music/ArtistPage.vue'),
          name: 'artist-albums',
          props: true
        }, {
          path: '/music/artists/:id/similar',
          component: () => import('pages/client/Music/ArtistPage.vue'),
          name: 'artist-similar',
          props: true
        }]
      }, {
        path: '/music/albums/:id',
        component: () => import('pages/client/Music/AlbumPage.vue'),
        name: 'album',
        props: true
      }, {
        path: '/music/playlists/:id',
        component: () => import('pages/client/Music/PlaylistPage.vue'),
        name: 'playlist',
        props: true,
        meta: {
          title: 'Playlists'
        }
      }]
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
