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
}

export interface ITaskManagerSettings {
  defaultViewMode: TasksViewModeEnum
}

export interface IGallerySettings {
  gridSize: GalleryGridSize
}

export interface IMusicSettings {
  /** Percent of track duration that must be listened before scrobbling to history. */
  scrobblePercent: number
}

export interface IAppSettings {
  dashboard: IDashboardSettings
  lifelog: ILifelogSettings
  taskManager: ITaskManagerSettings
  gallery: IGallerySettings
  music: IMusicSettings
}
