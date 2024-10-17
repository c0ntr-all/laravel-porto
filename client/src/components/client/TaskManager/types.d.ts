export interface IUser {
  id: string
  name: string
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
  completed: boolean
  relationships: {
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
  title: string
  relationships: {
    tasks?: {
      data: ITask[]
    }
  }
}
