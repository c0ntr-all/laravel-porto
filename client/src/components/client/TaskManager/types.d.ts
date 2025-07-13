import { IIncludedItem } from 'src/components/types'

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

export interface IProgressItem {
  id: string
  task_id: string
  title: string
  content: string
  is_final: boolean
  finished_at: string
  created_at: string
  updated_at: string
}

export interface IReminderItem {
  id: string
  task_id: string
  user_id: string
  is_active: boolean
  interval: string
  to_remind_before: string
  datetime: string
  created_at: string
  updated_at: string
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
  is_declined: boolean
  relationships: {
    checklists?: {
      data: IChecklist[],
      meta: {
        count: number
      }
    },
    progress?: {
      data: IProgressItem[],
      meta: {
        count: number
      }
    },
    reminders?: {
      data: IReminderItem[],
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

interface ICreateTaskResponse {
  data: {
    type: string
    id: string
    attributes: {
      title: string
      completed: boolean
      content?: string
      created_at?: string
      relationships: {
        comments: {
          data: IComment[]
        }
      }
    }
  },
  meta: {
    message?: string
  }
}

interface IUpdateTaskResponse {
  data: {
    type: string
    id: string
    attributes: {
      title: string
      content?: string
      finished_at: string
      created_at?: string
      comments?: IComment[]
    }
  },
  meta: {
    message?: string
  }
}

interface IDeclineTaskResponse {
  data: {
    type: string
    id: string
    attributes: {
      is_declined: boolean
    }
  },
  meta: {
    message?: string
  }
}

interface IDeleteTaskResponse {
  meta: {
    message?: string
  }
}

interface ICreateCommentResponse {
  data: {
    type: string
    id: string
    attributes: {
      name: string
      content: string
      created_at: string
    }
    relationships: {
      user: {
        data: IUser
      }
    }
  },
  included: IIncludedItem[]
  meta: {
    message?: string
  }
}

interface ICreateChecklistResponse {
  data: {
    type: string
    id: string
    attributes: {
      title: string
      created_at: string
      updated_at: string
    }
  },
  included: IIncludedItem[]
  meta: {
    message?: string
  }
}

interface ICreateProgressResponse {
  data: {
    type: string
    id: string
    attributes: {
      task_id: string
      title: string
      content: string
      is_final: boolean
      finished_at: string
      created_at: string
      updated_at: string
    }
  },
  meta: {
    message?: string
  }
}

interface ICreateReminderResponse {
  data: {
    type: string
    id: string
    attributes: {
      task_id: string
      user_id: string
      to_remind_before: string
      interval: string
      is_active: boolean
      datetime: string
      created_at: string
    }
  },
  meta: {
    message?: string
  }
}
