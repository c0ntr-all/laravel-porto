import { reactive } from 'vue'
import { camel } from 'radash'
import {
  ITaskList, ITask, IProgress, IChecklist, IChecklistItem,
  IReminderItem, IReminderOccurrence,
  IUseCaseLog, IComment, IPostAttachment
} from 'src/types'
import { IUser } from 'src/types/user'
import { StoreEntity } from 'src/types/store'
import { upsertEntity } from 'src/utils/jsonapi'
import { mapTaskAttachments } from 'src/api/mappers/attachment.mapper'

export type TaskCollections = {
  taskLists: StoreEntity<ITaskList>
  tasks: StoreEntity<ITask>
  progress: StoreEntity<IProgress>
  checklists: StoreEntity<IChecklist>
  checklistItems: StoreEntity<IChecklistItem>
  reminder: StoreEntity<IReminderItem>
  reminderOccurrences: StoreEntity<IReminderOccurrence>
  comments: StoreEntity<IComment>
  useCaseLogs: StoreEntity<IUseCaseLog>
  users: StoreEntity<IUser>
  attachments: StoreEntity<IPostAttachment>
}

export type TaskStoreContext = {
  state: TaskCollections
  collections: TaskCollections
  isValidCollectionKey: (key: string) => key is keyof TaskCollections
  collectionKeyForType: (type: string) => keyof TaskCollections | null
  relatedItems: (related: Record<string, unknown>, key: string) => unknown[]
  upsertMappedAttachments: (items: unknown[]) => IPostAttachment[]
}

export function createTaskCollections(): TaskStoreContext {
  const taskLists = reactive<StoreEntity<ITaskList>>({ byId: {}, allIds: [] })
  const tasks = reactive<StoreEntity<ITask>>({ byId: {}, allIds: [] })
  const progress = reactive<StoreEntity<IProgress>>({ byId: {}, allIds: [] })
  const checklists = reactive<StoreEntity<IChecklist>>({ byId: {}, allIds: [] })
  const checklistItems = reactive<StoreEntity<IChecklistItem>>({ byId: {}, allIds: [] })
  const reminder = reactive<StoreEntity<IReminderItem>>({ byId: {}, allIds: [] })
  const reminderOccurrences = reactive<StoreEntity<IReminderOccurrence>>({ byId: {}, allIds: [] })
  const comments = reactive<StoreEntity<IComment>>({ byId: {}, allIds: [] })
  const useCaseLogs = reactive<StoreEntity<IUseCaseLog>>({ byId: {}, allIds: [] })
  const users = reactive<StoreEntity<IUser>>({ byId: {}, allIds: [] })
  const attachments = reactive<StoreEntity<IPostAttachment>>({ byId: {}, allIds: [] })

  // Словарь для доступа по имени
  const collections: TaskCollections = {
    taskLists,
    tasks,
    progress,
    checklists,
    checklistItems,
    reminder,
    reminderOccurrences,
    comments,
    useCaseLogs,
    users,
    attachments
  }

  // Type guard для проверки существования ключа в collections
  function isValidCollectionKey(key: string): key is keyof TaskCollections {
    return key in collections
  }

  function collectionKeyForType(type: string): keyof TaskCollections | null {
    if (type === 'reminders') return 'reminder'
    const camelType = camel(type)
    return isValidCollectionKey(camelType) ? camelType : null
  }

  function relatedItems(related: Record<string, unknown>, key: string): unknown[] {
    const value = related[key]
    if (!value) return []
    if (Array.isArray(value)) return value
    return Object.values(value as Record<string, unknown>)
  }

  function upsertMappedAttachments(items: unknown[]): IPostAttachment[] {
    const mapped = mapTaskAttachments(items)
    for (const item of mapped) {
      upsertEntity(attachments, item)
    }
    return mapped
  }

  return {
    state: collections,
    collections,
    isValidCollectionKey,
    collectionKeyForType,
    relatedItems,
    upsertMappedAttachments
  }
}
