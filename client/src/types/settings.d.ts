import { LifeLogViewMode } from 'src/types/LifeLog/filter'
import { TasksViewModeEnum } from 'src/enums/TaskManager/TasksViewModeEnum'

export type GalleryGridSize = 's' | 'm' | 'l'

export interface IDashboardSettings {
  showWelcome: boolean
  showUpcomingTasks: boolean
  showRecentPosts: boolean
  showNowPlaying: boolean
  compactWidgets: boolean
}

export interface ILifelogSettings {
  defaultViewMode: LifeLogViewMode
  showTimeline: boolean
}

export interface ITaskManagerSettings {
  defaultViewMode: TasksViewModeEnum
}

export interface IGallerySettings {
  gridSize: GalleryGridSize
}

export interface IAppSettings {
  dashboard: IDashboardSettings
  lifelog: ILifelogSettings
  taskManager: ITaskManagerSettings
  gallery: IGallerySettings
}
