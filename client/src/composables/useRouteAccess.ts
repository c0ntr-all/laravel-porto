import { watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from 'src/stores/modules/userStore'

const FALLBACK_ROUTE = 'dashboard'

export function useRouteAccess(): void {
  const route = useRoute()
  const router = useRouter()
  const userStore = useUserStore()

  watch(
    () => userStore.roles,
    () => {
      if (route.name === FALLBACK_ROUTE || userStore.hasRouteAccess(route.matched)) {
        return
      }

      void router.replace({ name: FALLBACK_ROUTE })
    }
  )
}
