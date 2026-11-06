import { IJsonApiResponse } from 'src/types'
import { extractCursorFromResponse, hasMoreFromResponse } from 'src/utils/apiQuery'
import { mergeById } from 'src/utils/collection'

export interface ListRequest {
  begin: () => number
  current: () => number
  isCurrent: (id: number) => boolean
}

export function createListRequest(): ListRequest {
  let current = 0

  return {
    begin() {
      current += 1
      return current
    },
    current() {
      return current
    },
    isCurrent(id: number) {
      return id === current
    }
  }
}

/**
 * Один протокол для списков: свежая загрузка отменяет предыдущую,
 * догрузка по курсору не сбрасывает флаги более новой загрузки.
 * `begin()` вызывается только для свежей загрузки, как в прежних счётчиках.
 */
export async function withListRequest(
  request: ListRequest,
  append: boolean,
  lifecycle: {
    canAppend: boolean
    onAppend: () => void
    onFresh: () => void
    onSettle: () => void
  },
  run: (isCurrent: () => boolean) => Promise<void>
): Promise<void> {
  if (append) {
    if (!lifecycle.canAppend) {
      return
    }

    lifecycle.onAppend()
  } else {
    request.begin()
    lifecycle.onFresh()
  }

  const requestId = request.current()
  const isCurrent = () => request.isCurrent(requestId)

  try {
    await run(isCurrent)
  } finally {
    if (isCurrent()) {
      lifecycle.onSettle()
    }
  }
}

export function mergeCursorPage<T extends { id: string }>(
  current: T[],
  incoming: T[],
  response: IJsonApiResponse,
  append: boolean
): { items: T[], cursor: string | null, hasMore: boolean } {
  return {
    items: append ? mergeById(current, incoming) : incoming,
    cursor: extractCursorFromResponse(response),
    hasMore: hasMoreFromResponse(response)
  }
}
