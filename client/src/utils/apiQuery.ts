import { IJsonApiResponse } from 'src/types'

/**
 * Строит query в формате Spatie Query Builder: filter[tags]=1,2,3.
 * Пустые массивы, null, undefined и пустые строки пропускаются.
 */
export function buildFilterForUrl(filters: Record<string, any>): string {
  const params = new URLSearchParams()

  for (const [key, value] of Object.entries(filters)) {
    if (Array.isArray(value)) {
      if (value.length === 0) {
        continue
      }
      params.append(`filter[${key}]`, value.join(','))
    } else if (value !== undefined && value !== null && value !== '') {
      params.append(`filter[${key}]`, String(value))
    }
  }

  return params.toString()
}

export function extractCursorFromLink(link?: string | { href?: string } | null): string | null {
  if (!link) {
    return null
  }

  const href = typeof link === 'string' ? link : link.href
  if (!href) {
    return null
  }

  try {
    return new URL(href, 'http://localhost').searchParams.get('cursor')
  } catch {
    return null
  }
}

export function extractCursorFromResponse(response: IJsonApiResponse): string | null {
  return response.meta?.next_cursor ??
    response.meta?.cursor?.next ??
    extractCursorFromLink(response.links?.next) ??
    extractCursorFromLink(response.meta?.next_page_url)
}

export function hasMoreFromResponse(response: IJsonApiResponse): boolean {
  if (typeof response.meta?.has_more === 'boolean') {
    return response.meta.has_more
  }

  return Boolean(extractCursorFromResponse(response))
}
