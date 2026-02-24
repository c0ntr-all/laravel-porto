import { InjectionKey } from 'vue'
import { IChecklistItem } from 'src/types'

export type createChecklistItem = (title: string) => Promise<void>
export type updateChecklistItem = (itemId: string, patch: Partial<IChecklistItem>) => Promise<void>
export type deleteChecklistItem = (itemId: string) => Promise<void>
export const createChecklistItemKey: InjectionKey<createChecklistItem> = Symbol('createChecklistItem')
export const updateChecklistItemKey: InjectionKey<updateChecklistItem> = Symbol('updateChecklistItem')
export const deleteChecklistItemKey: InjectionKey<deleteChecklistItem> = Symbol('deleteChecklistItem')
