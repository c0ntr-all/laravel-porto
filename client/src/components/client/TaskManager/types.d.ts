export interface IUser {
  id: string
  name: string
}
export interface IChecklistItem {
  id: string
  title: string
  created_at: string
  finished_at: string | null
}
export interface IChecklist {
  id: string
  title: string
  created_at: string
  updated_at: string
  relationships: {
    checklistItems: {
      data: IChecklistItem[],
      meta: {
        count: number
      }
    }
  }
}
export interface IComment {
  id: string
  content: string
  created_at: string
  relationships: {
    user: {
      data: IUser
    }
  }
}
export interface ITask {
  id: string
  title: string
  content?: string
  finished_at: string | null
  relationships: {
    checklists?: {
      data: IChecklist[],
      meta: {
        count: number
      }
    },
    comments: {
      data: IComment[],
      meta: {
        count: number
      }
    }
  }
}
export interface ITaskList {
  id: string
  attributes: {
    title: string
  }
  relationships: {
    tasks?: {
      data: ITask[]
    }
  }
}
