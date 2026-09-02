import { computed, reactive } from 'vue'
import { defineStore } from 'pinia'
import { LifeLogViewModeEnum } from 'src/enums/LifeLog/LifeLogViewModeEnum'
import { TasksViewModeEnum } from 'src/enums/TaskManager/TasksViewModeEnum'
import {
  GalleryGridSize,
  IAppSettings,
  IDashboardSettings,
  IGallerySettings,
  ILifelogSettings,
  ITaskManagerSettings
} from 'src/types/settings'

const SETTINGS_STORAGE_KEY = 'home-portal.settings'
const LEGACY_TASK_VIEW_KEY = 'taskManager.tasksViewMode'

const GALLERY_GRID_MIN: Record<GalleryGridSize, number> = {
  s: 180,
  m: 220,
  l: 280
}

function defaultSettings(): IAppSettings {
  return {
    dashboard: {
      showWelcome: true,
      showUpcomingTasks: true,
      showRecentPosts: true,
      showNowPlaying: true,
      compactWidgets: false
    },
    lifelog: {
      defaultViewMode: LifeLogViewModeEnum.Expanded
    },
    taskManager: {
      defaultViewMode: TasksViewModeEnum.BLOCKS
    },
    gallery: {
      gridSize: 'm'
    }
  }
}

function isGalleryGridSize(value: unknown): value is GalleryGridSize {
  return value === 's' || value === 'm' || value === 'l'
}

function isTasksViewMode(value: unknown): value is TasksViewModeEnum {
  return value === TasksViewModeEnum.BLOCKS ||
    value === TasksViewModeEnum.LIST ||
    value === TasksViewModeEnum.REMINDERS
}

function readLegacyTaskViewMode(): TasksViewModeEnum | null {
  if (typeof window === 'undefined') {
    return null
  }

  const stored = localStorage.getItem(LEGACY_TASK_VIEW_KEY)
  return stored === TasksViewModeEnum.LIST || stored === TasksViewModeEnum.BLOCKS
    ? stored
    : null
}

function mergeSettings(raw: Partial<IAppSettings> | null): IAppSettings {
  const defaults = defaultSettings()
  const legacyTaskView = readLegacyTaskViewMode()

  return {
    dashboard: {
      ...defaults.dashboard,
      ...(raw?.dashboard ?? {})
    },
    lifelog: {
      ...defaults.lifelog,
      ...(raw?.lifelog ?? {})
    },
    taskManager: {
      ...defaults.taskManager,
      ...(raw?.taskManager ?? {}),
      defaultViewMode: isTasksViewMode(raw?.taskManager?.defaultViewMode)
        ? raw.taskManager.defaultViewMode
        : legacyTaskView ?? defaults.taskManager.defaultViewMode
    },
    gallery: {
      ...defaults.gallery,
      ...(raw?.gallery ?? {}),
      gridSize: isGalleryGridSize(raw?.gallery?.gridSize)
        ? raw.gallery.gridSize
        : defaults.gallery.gridSize
    }
  }
}

function readSettings(): IAppSettings {
  if (typeof window === 'undefined') {
    return defaultSettings()
  }

  try {
    const raw = localStorage.getItem(SETTINGS_STORAGE_KEY)
    return mergeSettings(raw ? JSON.parse(raw) as Partial<IAppSettings> : null)
  } catch {
    return defaultSettings()
  }
}

function writeSettings(settings: IAppSettings): void {
  if (typeof window === 'undefined') {
    return
  }

  localStorage.setItem(SETTINGS_STORAGE_KEY, JSON.stringify(settings))
  localStorage.setItem(LEGACY_TASK_VIEW_KEY, settings.taskManager.defaultViewMode)
}

export const useSettingsStore = defineStore('settings', () => {
  const settings = reactive<IAppSettings>(readSettings())

  const galleryCardMin = computed(() => GALLERY_GRID_MIN[settings.gallery.gridSize])

  function persist(): void {
    writeSettings(settings)
  }

  function updateDashboard(patch: Partial<IDashboardSettings>): void {
    Object.assign(settings.dashboard, patch)
    persist()
  }

  function updateLifelog(patch: Partial<ILifelogSettings>): void {
    Object.assign(settings.lifelog, patch)
    persist()
  }

  function updateTaskManager(patch: Partial<ITaskManagerSettings>): void {
    Object.assign(settings.taskManager, patch)
    persist()
  }

  function updateGallery(patch: Partial<IGallerySettings>): void {
    Object.assign(settings.gallery, patch)
    persist()
  }

  function resetSection(section: keyof IAppSettings): void {
    const defaults = defaultSettings()
    Object.assign(settings[section], defaults[section])
    persist()
  }

  return {
    settings,
    galleryCardMin,
    updateDashboard,
    updateLifelog,
    updateTaskManager,
    updateGallery,
    resetSection
  }
})
