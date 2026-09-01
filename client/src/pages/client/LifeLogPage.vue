<template>
  <div class="lifelog-page">
    <LifeLogToolbar
      :view-mode="viewMode"
      :show-timeline="showTimeline"
      @create-post="openCreatePostModal"
      @update:view-mode="setViewMode"
      @update:show-timeline="setShowTimeline"
    />

    <div class="lifelog-page__grid">
      <aside class="lifelog-page__sidebar">
        <LifeLogFilterPanel
          v-model="filter"
          :all-tags="allTags"
          :presets="presets"
          @submit="onFilterSubmit"
          @reset="onFilterReset"
        />

        <q-expansion-item
          v-if="showTimeline"
          icon="account_tree"
          label="Граф диапазонов"
          default-opened
          class="lifelog-page__timeline q-mt-md"
        >
          <LifeLogTimelineGraph
            :posts="filteredPosts"
            :presets="presets"
          />
        </q-expansion-item>

        <LifeLogPresetsSection class="q-mt-md" />
      </aside>

      <main class="lifelog-page__content">
        <LifeLogPostsList
          :posts="filteredPosts"
          :view-mode="viewMode"
          :is-loading="isLoading"
          :is-post-expanded="isPostExpanded"
          :toggle-post-expanded="togglePostExpanded"
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
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { usePresetStore } from 'src/stores/modules/presetStore'
import { useLifeLogFilters } from 'src/composables/client/Lifelog/useLifeLogFilters'
import { useLifeLogView } from 'src/composables/client/Lifelog/useLifeLogView'
import LifeLogToolbar from 'src/components/client/LifeLog/layout/LifeLogToolbar.vue'
import LifeLogFilterPanel from 'src/components/client/LifeLog/layout/LifeLogFilterPanel.vue'
import LifeLogTimelineGraph from 'src/components/client/LifeLog/timeline/LifeLogTimelineGraph.vue'
import LifeLogPostsList from 'src/components/client/LifeLog/posts/LifeLogPostsList.vue'
import LifeLogPresetsSection from 'src/components/client/LifeLog/LifeLogPresetsSection.vue'
import PostFormCreate from 'src/components/client/LifeLog/forms/PostFormCreate.vue'
import AppModal from 'src/components/default/AppModal.vue'
import { ILifeLogFilter } from 'src/types'

const presetStore = usePresetStore()
const { presets } = storeToRefs(presetStore)

const {
  filter,
  allTags,
  filteredPosts,
  isLoading,
  loadTags,
  loadPresets,
  loadPosts,
  applyFilter,
  resetFilter
} = useLifeLogFilters()

const {
  viewMode,
  showTimeline,
  setViewMode,
  setShowTimeline,
  isPostExpanded,
  togglePostExpanded
} = useLifeLogView()

const isCreatePostModalOpen = ref(false)

const openCreatePostModal = () => {
  isCreatePostModalOpen.value = true
}

const onPostCreated = async () => {
  isCreatePostModalOpen.value = false
  await loadPosts()
}

const onFilterSubmit = async (nextFilter: ILifeLogFilter) => {
  await applyFilter(nextFilter)
}

const onFilterReset = async () => {
  await resetFilter()
}

onMounted(async () => {
  await Promise.all([
    loadTags(),
    loadPresets(),
    loadPosts()
  ])
})
</script>

<style lang="scss" scoped>
.lifelog-page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 8px 24px;

  &__grid {
    display: grid;
    grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
    gap: 16px;
    align-items: start;
  }

  &__sidebar {
    position: sticky;
    top: 12px;
  }

  &__timeline {
    border: 1px solid #e4e7eb;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
  }

  @media (max-width: 960px) {
    &__grid {
      grid-template-columns: 1fr;
    }

    &__sidebar {
      position: static;
    }
  }
}
</style>
