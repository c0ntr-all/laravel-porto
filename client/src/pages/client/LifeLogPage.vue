<template>
  <div class="lifelog-page">
    <LifeLogToolbar
      :view-mode="viewMode"
      @create-post="openCreatePostModal"
      @update:view-mode="setViewMode"
    />

    <div class="lifelog-page__grid">
      <aside ref="sidebarRef" class="lifelog-page__sidebar">
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

      <main ref="contentRef" class="lifelog-page__content">
        <LifeLogPostsList
          :posts="filteredPosts"
          :presets="presets"
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
import { nextTick, onMounted, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { usePresetStore } from 'src/stores/modules/presetStore'
import { useLifeLogFilters } from 'src/composables/client/Lifelog/useLifeLogFilters'
import { useLifeLogView } from 'src/composables/client/Lifelog/useLifeLogView'
import { useLifeLogSidebarScroll } from 'src/composables/client/Lifelog/useLifeLogSidebarScroll'
import { mapPresetToLifeLogFilter } from 'src/utils/LifeLog/filter.mapper'
import LifeLogToolbar from 'src/components/client/LifeLog/layout/LifeLogToolbar.vue'
import LifeLogFilterPanel from 'src/components/client/LifeLog/layout/LifeLogFilterPanel.vue'
import LifeLogPostsList from 'src/components/client/LifeLog/posts/LifeLogPostsList.vue'
import LifeLogPresetsSection from 'src/components/client/LifeLog/LifeLogPresetsSection.vue'
import PostFormCreate from 'src/components/client/LifeLog/forms/PostFormCreate.vue'
import AppModal from 'src/components/default/AppModal.vue'
import { ILifeLogFilter, IPreset } from 'src/types'

const presetStore = usePresetStore()
const { presets } = storeToRefs(presetStore)

const sidebarRef = ref<HTMLElement | null>(null)
const contentRef = ref<HTMLElement | null>(null)

const {
  filter,
  allTags,
  filteredPosts,
  isLoading,
  initialize,
  applyFilter,
  resetFilter
} = useLifeLogFilters()

const {
  viewMode,
  setViewMode,
  isPostExpanded,
  togglePostExpanded
} = useLifeLogView()

const { syncSidebarScroll } = useLifeLogSidebarScroll(sidebarRef, contentRef)

const isCreatePostModalOpen = ref(false)

const openCreatePostModal = () => {
  isCreatePostModalOpen.value = true
}

const onPostCreated = async () => {
  isCreatePostModalOpen.value = false
  await applyFilter(filter.value, { syncRoute: false })
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

watch(
  () => [filteredPosts.value.length, isLoading.value],
  () => {
    void nextTick(syncSidebarScroll)
  }
)
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
    position: sticky;
    top: 12px;
    max-height: calc(100vh - 96px);
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
