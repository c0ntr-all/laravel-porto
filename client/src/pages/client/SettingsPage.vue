<template>
  <div class="settings-page">
    <q-card class="settings-nav" flat>
      <q-list padding>
        <q-item
          v-for="section in sections"
          :key="section.name"
          :to="{ name: section.name }"
          class="settings-nav__item"
          clickable
          v-ripple
        >
          <q-item-section avatar>
            <div class="settings-nav__icon" :style="{ background: section.background, color: section.color }">
              <q-icon :name="section.icon" />
            </div>
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ section.label }}</q-item-label>
            <q-item-label caption>{{ section.caption }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-card>

    <q-card class="settings-content" flat>
      <q-card-section>
        <router-view />
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup lang="ts">
const sections = [
  {
    name: 'settings-dashboard',
    label: 'Dashboard',
    caption: 'Виджеты и сетка',
    icon: 'home',
    color: '#6C5FFC',
    background: 'rgba(108, 95, 252, 0.12)'
  },
  {
    name: 'settings-lifelog',
    label: 'Lifelog',
    caption: 'Посты и лента',
    icon: 'history_edu',
    color: '#1f7a72',
    background: 'rgba(38, 166, 154, 0.14)'
  },
  {
    name: 'settings-task-manager',
    label: 'Task Manager',
    caption: 'Отображение задач',
    icon: 'list',
    color: '#c4451a',
    background: 'rgba(242, 192, 55, 0.2)'
  },
  {
    name: 'settings-music',
    label: 'Music',
    caption: 'Плеер и очередь',
    icon: 'music_note',
    color: '#7b1fa2',
    background: 'rgba(156, 39, 176, 0.12)'
  },
  {
    name: 'settings-gallery',
    label: 'Gallery',
    caption: 'Сетка альбомов',
    icon: 'collections',
    color: '#1565c0',
    background: 'rgba(33, 150, 243, 0.12)'
  }
]
</script>

<style lang="scss" scoped>
.settings-page {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 16px;
  align-items: start;
  max-width: 1080px;

  @media (max-width: 900px) {
    grid-template-columns: 1fr;
  }
}

.settings-nav {
  border-radius: 16px;

  &__item {
    border-radius: 12px;
    margin-bottom: 4px;
  }

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
  }
}

.settings-content {
  border-radius: 16px;
  min-height: 360px;
}

:deep(.q-router-link--active) {
  color: inherit;
  background: rgba(108, 95, 252, 0.08);
}

:deep(.settings-panel__intro) {
  margin-bottom: 20px;
}

:deep(.settings-panel__title) {
  font-size: 18px;
  font-weight: 600;
  color: #282f53;
}

:deep(.settings-panel__text) {
  margin: 6px 0 0;
  color: #777a8f;
  font-size: 13px;
  line-height: 1.45;
}

:deep(.settings-panel__block) {
  margin-bottom: 20px;
}

:deep(.settings-panel__label) {
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #282f53;
}

:deep(.settings-panel__list) {
  border: 1px solid #e9edf4;
  border-radius: 12px;
  overflow: hidden;
}

:deep(.settings-panel__volume) {
  display: flex;
  align-items: center;
  gap: 12px;
}

:deep(.settings-panel__volume-value) {
  width: 48px;
  color: #777a8f;
  font-size: 13px;
}
</style>
