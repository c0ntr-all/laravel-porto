import { computed } from 'vue'
import { useRouter, type RouteRecordRaw } from 'vue-router'
import { SidebarSectionEnum } from 'src/enums/Navigation/SidebarSectionEnum'
import { useUserStore } from 'src/stores/modules/userStore'

function sidebarSection(route: RouteRecordRaw): SidebarSectionEnum | null {
  if (route.meta?.sidebar) {
    return route.meta.sidebar
  }

  if (route.meta?.menu) {
    return SidebarSectionEnum.Main
  }

  return null
}

function layoutChildren(routes: readonly RouteRecordRaw[]): RouteRecordRaw[] {
  const layout = routes.find(route => route.name === 'main') ?? routes[0]
  return layout?.children ?? []
}

export function useSidebarMenu() {
  const router = useRouter()
  const userStore = useUserStore()

  const itemsBySection = computed(() => {
    const sections: Record<SidebarSectionEnum, RouteRecordRaw[]> = {
      [SidebarSectionEnum.Main]: [],
      [SidebarSectionEnum.Admin]: []
    }

    for (const route of layoutChildren(router.options.routes)) {
      const section = sidebarSection(route)
      if (!section || !userStore.hasAccess(route.meta?.roles)) {
        continue
      }

      sections[section].push(route)
    }

    return sections
  })

  const menuItems = computed(() => itemsBySection.value[SidebarSectionEnum.Main])
  const adminItems = computed(() => itemsBySection.value[SidebarSectionEnum.Admin])

  return {
    menuItems,
    adminItems
  }
}
