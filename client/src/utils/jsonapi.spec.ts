import { describe, expect, it } from 'vitest'
import { IJsonApiResponse } from 'src/types'
import {
  buildFilterForUrl,
  extractCursorFromLink,
  extractCursorFromResponse,
  hasMoreFromResponse
} from 'src/utils/jsonapi'

function response(partial: Partial<IJsonApiResponse> = {}): IJsonApiResponse {
  return {
    data: [],
    ...partial
  }
}

describe('buildFilterForUrl', () => {
  it('joins array values with commas under filter[key]', () => {
    expect(buildFilterForUrl({ tags: [1, 2, 3], author: 'John' })).toBe(
      'filter%5Btags%5D=1%2C2%2C3&filter%5Bauthor%5D=John'
    )
  })

  it('skips empty arrays, null, undefined, and empty strings', () => {
    expect(buildFilterForUrl({
      tags: [],
      author: null,
      q: undefined,
      note: '',
      status: 'open'
    })).toBe('filter%5Bstatus%5D=open')
  })

  it('stringifies scalar values, including 0 and false', () => {
    expect(buildFilterForUrl({ count: 0, active: false })).toBe(
      'filter%5Bcount%5D=0&filter%5Bactive%5D=false'
    )
  })

  it('encodes keys and values with URLSearchParams', () => {
    const expected = new URLSearchParams()
    expected.append('filter[q]', 'a&b=c d')
    expected.append('filter[tags]', ['a b', 'c'].join(','))

    expect(buildFilterForUrl({
      q: 'a&b=c d',
      tags: ['a b', 'c']
    })).toBe(expected.toString())
  })
})

describe('extractCursorFromLink', () => {
  it('reads cursor from a string URL, including a relative one', () => {
    expect(extractCursorFromLink('https://example.com/items?page=2&cursor=abc')).toBe('abc')
    expect(extractCursorFromLink('/v1/items?cursor=abc')).toBe('abc')
  })

  it('reads cursor from an href object', () => {
    expect(extractCursorFromLink({ href: 'https://example.com/items?cursor=xyz' })).toBe('xyz')
  })

  it('returns null for null, undefined, and missing href', () => {
    expect(extractCursorFromLink(null)).toBeNull()
    expect(extractCursorFromLink(undefined)).toBeNull()
    expect(extractCursorFromLink('')).toBeNull()
    expect(extractCursorFromLink({})).toBeNull()
    expect(extractCursorFromLink({ href: '' })).toBeNull()
  })

  it('returns null when the cursor query param is absent', () => {
    expect(extractCursorFromLink('https://example.com/items?page=2')).toBeNull()
  })

  it('returns null for a URL that cannot be parsed', () => {
    expect(extractCursorFromLink('http://[')).toBeNull()
    expect(extractCursorFromLink({ href: 'http://[' })).toBeNull()
  })
})

describe('extractCursorFromResponse', () => {
  it('prefers meta.next_cursor over later sources', () => {
    expect(extractCursorFromResponse(response({
      meta: {
        next_cursor: 'from-meta',
        cursor: { next: 'from-cursor' },
        next_page_url: 'https://example.com/items?cursor=from-page'
      },
      links: { next: 'https://example.com/items?cursor=from-link' }
    }))).toBe('from-meta')
  })

  it('uses meta.cursor.next when next_cursor is null', () => {
    expect(extractCursorFromResponse(response({
      meta: {
        next_cursor: null,
        cursor: { next: 'from-cursor' },
        next_page_url: 'https://example.com/items?cursor=from-page'
      },
      links: { next: 'https://example.com/items?cursor=from-link' }
    }))).toBe('from-cursor')
  })

  it('uses links.next when cursor fields are missing', () => {
    expect(extractCursorFromResponse(response({
      meta: {
        next_page_url: 'https://example.com/items?cursor=from-page'
      },
      links: { next: 'https://example.com/items?cursor=from-link' }
    }))).toBe('from-link')
  })

  it('uses meta.next_page_url when earlier cursors are absent', () => {
    expect(extractCursorFromResponse(response({
      links: { next: 'http://[' },
      meta: {
        next_page_url: 'https://example.com/items?cursor=from-page'
      }
    }))).toBe('from-page')

    expect(extractCursorFromResponse(response({
      meta: {
        next_page_url: 'https://example.com/items?cursor=from-page'
      }
    }))).toBe('from-page')
  })

  it('returns null when no cursor source is present', () => {
    expect(extractCursorFromResponse(response())).toBeNull()
    expect(extractCursorFromResponse(response({
      meta: { cursor: { next: null }, next_cursor: null, next_page_url: null },
      links: { next: null }
    }))).toBeNull()
  })
})

describe('hasMoreFromResponse', () => {
  it('returns an explicit boolean meta.has_more even when a cursor disagrees', () => {
    expect(hasMoreFromResponse(response({
      meta: { has_more: true }
    }))).toBe(true)

    expect(hasMoreFromResponse(response({
      meta: { has_more: false, next_cursor: 'still-here' }
    }))).toBe(false)
  })

  it('is true when has_more is omitted and a cursor exists', () => {
    expect(hasMoreFromResponse(response({
      meta: { cursor: { next: 'next' } }
    }))).toBe(true)
  })

  it('is false when has_more is omitted and no cursor exists', () => {
    expect(hasMoreFromResponse(response())).toBe(false)
    expect(hasMoreFromResponse(response({ meta: {} }))).toBe(false)
  })

  it('ignores a non-boolean has_more and falls back to the cursor', () => {
    expect(hasMoreFromResponse({
      data: [],
      meta: { has_more: 1, next_cursor: 'abc' }
    } as unknown as IJsonApiResponse)).toBe(true)

    expect(hasMoreFromResponse({
      data: [],
      meta: { has_more: 'yes' }
    } as unknown as IJsonApiResponse)).toBe(false)
  })
})
