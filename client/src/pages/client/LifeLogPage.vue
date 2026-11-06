<template>
  <div class="lifelog-page">
    <LifeLogToolbar
      :view-mode="viewMode"
      @create-post="openCreatePostModal"
      @create-movie-watch="openMovieWatchModal"
      @update:view-mode="setViewMode"
    />

    <div class="lifelog-page__grid">
      <aside class="lifelog-page__sidebar">
        <LifeLogFilterPanel
          v-model="filter"
          :all-tags="allTags"
          @submit="onFilterSubmit"
          @reset="onFilterReset"
        />

        <LifeLogPresetsSection
          class="q-mt-md"
          :presets="presets"
          :active-preset-id="filter.activePresetId"
          @apply-preset="onPresetFilter"
        />
      </aside>

      <main class="lifelog-page__content">
        <LifeLogPostsTabs
          :model-value="postsTab"
          @update:model-value="onPostsTabChange"
        />

        <LifeLogPostsList
          :posts="filteredPosts"
          :presets="presets"
          :view-mode="viewMode"
          :is-loading="isLoading"
          :is-loading-more="isLoadingMore"
          :has-more-posts="hasMorePosts"
          :is-post-expanded="isPostExpanded"
          :toggle-post-expanded="togglePostExpanded"
          @load-more="loadMorePosts"
        />
      </main>
    </div>

    <AppModal
      v-model="isCreatePostModalOpen"
      width="700px"
      scrollable
    >
      <template #header>
        Создать пост
      </template>
      <template #body>
        <PostFormCreate
          v-if="isCreatePostModalOpen"
          @success="onPostCreated"
        />
      </template>
    </AppModal>

    <AppModal
      v-model="isMovieWatchModalOpen"
      width="520px"
      scrollable
    >
      <template #header>
        Просмотр фильма
      </template>
      <template #body>
        <PostFormMovieWatch
          v-if="isMovieWatchModalOpen"
          @success="onMovieWatchCreated"
        />
      </template>
    </AppModal>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { usePresetStore } from 'src/stores/modules/presetStore'
import { useLifeLogFilters } from 'src/composables/client/Lifelog/useLifeLogFilters'
import { useLifeLogView } from 'src/composables/client/Lifelog/useLifeLogView'
import { mapPresetToLifeLogFilter } from 'src/utils/LifeLog/filter.mapper'
import LifeLogToolbar from 'src/components/client/LifeLog/layout/LifeLogToolbar.vue'
import LifeLogFilterPanel from 'src/components/client/LifeLog/layout/LifeLogFilterPanel.vue'
import LifeLogPostsList from 'src/components/client/LifeLog/posts/LifeLogPostsList.vue'
import LifeLogPostsTabs from 'src/components/client/LifeLog/posts/LifeLogPostsTabs.vue'
import LifeLogPresetsSection from 'src/components/client/LifeLog/LifeLogPresetsSection.vue'
import PostFormCreate from 'src/components/client/LifeLog/forms/PostFormCreate.vue'
import PostFormMovieWatch from 'src/components/client/LifeLog/forms/PostFormMovieWatch.vue'
import AppModal from 'src/components/default/AppModal.vue'
import { LifeLogPostsTabId } from 'src/constants/LifeLog/postsTabs'
import { ILifeLogFilter, IPreset } from 'src/types'

const presetStore = usePresetStore()
const { presets } = storeToRefs(presetStore)

const {
  filter,
  postsTab,
  allTags,
  filteredPosts,
  isLoading,
  isLoadingMore,
  hasMorePosts,
  loadMorePosts,
  initialize,
  applyFilter,
  resetFilter,
  setPostsTab
} = useLifeLogFilters()

const {
  viewMode,
  setViewMode,
  isPostExpanded,
  togglePostExpanded
} = useLifeLogView()

const isCreatePostModalOpen = ref(false)
const isMovieWatchModalOpen = ref(false)

const openCreatePostModal = () => {
  isCreatePostModalOpen.value = true
}

const openMovieWatchModal = () => {
  isMovieWatchModalOpen.value = true
}

const onPostsTabChange = async (tab: LifeLogPostsTabId) => {
  await setPostsTab(tab)
}

const onPostCreated = () => {
  isCreatePostModalOpen.value = false
}

const onMovieWatchCreated = () => {
  isMovieWatchModalOpen.value = false
}

const onFilterSubmit = async (nextFilter: ILifeLogFilter) => {
  await applyFilter(nextFilter)
}

const onFilterReset = async () => {
  await resetFilter()
}

const onPresetFilter = async (preset: IPreset) => {
  await applyFilter(mapPresetToLifeLogFilter(preset, allTags.value))
}

onMounted(() => {
  void initialize()
})
</script>

<style lang="scss" scoped>
.lifelog-page {
  width: 100%;
  padding: 0 8px 24px;

  &__grid {
    display: grid;
    grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
    gap: 16px;
    align-items: start;
  }

  &__sidebar {
    // Топбар в MainLayout фиксированный, высота .header — 75px.
    position: sticky;
    top: calc(75px + 12px);
    max-height: calc(100vh - 75px - 24px);
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
  }

  @media (max-width: 960px) {
    &__grid {
      grid-template-columns: 1fr;
    }

    &__sidebar {
      position: static;
      max-height: none;
      overflow: visible;
    }
  }
}
</style>
