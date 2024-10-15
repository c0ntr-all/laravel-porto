export interface IComment {
  id: string
  user_name: string
  content: string
  created_at: string
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
