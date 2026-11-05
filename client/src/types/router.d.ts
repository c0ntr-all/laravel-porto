import type { SidebarSectionEnum } from 'src/enums/Navigation/SidebarSectionEnum'
import type { UserRoleEnum } from 'src/enums/User/UserRoleEnum'

declare module 'vue-router' {
  interface RouteMeta {
    title?: string
    icon?: string
    menu?: boolean
    exact?: boolean
    sidebar?: SidebarSectionEnum
    roles?: UserRoleEnum[]
  }
}
