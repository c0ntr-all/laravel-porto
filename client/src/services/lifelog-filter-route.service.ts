import {
  RouteLocationNormalizedLoaded,
  Router
} from 'vue-router'
import { ILifeLogFilter } from 'src/types'
import {
  areLocationQueriesEqual,
  serializeLifeLogFilterToQuery,
  stripLifeLogFilterQuery
} from 'src/utils/LifeLog/filter.url'

export async function syncLifeLogFilterToRoute(
  router: Router,
  route: RouteLocationNormalizedLoaded,
  filter: ILifeLogFilter
): Promise<void> {
  const filterQuery = serializeLifeLogFilterToQuery(filter)
  const nextQuery = {
    ...stripLifeLogFilterQuery(route.query),
    ...filterQuery
  }

  if (areLocationQueriesEqual(route.query, nextQuery)) {
    return
  }

  await router.replace({
    query: nextQuery
  })
}
