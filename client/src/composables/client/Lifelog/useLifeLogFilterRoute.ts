import { Ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ILifeLogFilter, IPreset } from 'src/types'
import { ITag } from 'src/types/tag'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import {
  areLifeLogFiltersEqual,
  parseLifeLogFilterFromQuery
} from 'src/utils/LifeLog/filter.url'
import { syncLifeLogFilterToRoute } from 'src/services/lifelog-filter-route.service'

interface UseLifeLogFilterRouteOptions {
  allTags: Ref<ITag[]>
  presets: Ref<IPreset[]>
  getFilter: () => ILifeLogFilter
  onFilterFromRoute: (filter: ILifeLogFilter) => Promise<void>
}

export function useLifeLogFilterRoute(options: UseLifeLogFilterRouteOptions) {
  const route = useRoute()
  const router = useRouter()
  let suppressRouteWatch = false

  function parseFromRoute(): ILifeLogFilter | null {
    return parseLifeLogFilterFromQuery(
      route.query,
      options.allTags.value,
      options.presets.value
    )
  }

  async function syncToRoute(filter: ILifeLogFilter): Promise<void> {
    suppressRouteWatch = true

    try {
      await syncLifeLogFilterToRoute(router, route, filter)
    } finally {
      suppressRouteWatch = false
    }
  }

  watch(
    () => route.query,
    async () => {
      if (suppressRouteWatch) {
        return
      }

      const parsed = parseFromRoute()
      const nextFilter = parsed ?? createEmptyLifeLogFilter()

      if (areLifeLogFiltersEqual(nextFilter, options.getFilter())) {
        return
      }

      await options.onFilterFromRoute(nextFilter)
    }
  )

  return {
    parseFromRoute,
    syncToRoute
  }
}
