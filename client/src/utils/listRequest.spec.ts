import { describe, expect, it, vi } from 'vitest'
import { mergeById } from 'src/utils/collection'
import { createListRequest, mergeCursorPage, withListRequest } from 'src/utils/listRequest'
import { IJsonApiResponse } from 'src/types'

describe('mergeById', () => {
  it('appends only ids that are not already present', () => {
    const current = [{ id: '1', name: 'a' }, { id: '2', name: 'b' }]
    const incoming = [{ id: '2', name: 'other' }, { id: '3', name: 'c' }]

    expect(mergeById(current, incoming)).toEqual([
      { id: '1', name: 'a' },
      { id: '2', name: 'b' },
      { id: '3', name: 'c' }
    ])
  })
})

describe('mergeCursorPage', () => {
  const response = {
    data: [],
    meta: { next_cursor: 'next', has_more: true }
  } as IJsonApiResponse

  it('replaces the page when this is a fresh load', () => {
    const page = mergeCursorPage(
      [{ id: 'old' }],
      [{ id: 'new' }],
      response,
      false
    )

    expect(page.items).toEqual([{ id: 'new' }])
    expect(page.cursor).toBe('next')
    expect(page.hasMore).toBe(true)
  })

  it('keeps existing rows when appending', () => {
    const page = mergeCursorPage(
      [{ id: 'old' }],
      [{ id: 'old' }, { id: 'new' }],
      response,
      true
    )

    expect(page.items).toEqual([{ id: 'old' }, { id: 'new' }])
  })
})

describe('withListRequest', () => {
  it('drops a stale append when a newer load has started', async () => {
    const request = createListRequest()
    const settled: string[] = []
    let releaseFirst: (() => void) | undefined
    const first = new Promise<void>(resolve => {
      releaseFirst = resolve
    })

    const older = withListRequest(request, false, {
      canAppend: true,
      onAppend: () => undefined,
      onFresh: () => undefined,
      onSettle: () => settled.push('old')
    }, async () => {
      await first
    })

    await withListRequest(request, false, {
      canAppend: true,
      onAppend: () => undefined,
      onFresh: () => undefined,
      onSettle: () => settled.push('new')
    }, async () => undefined)

    releaseFirst?.()
    await older

    expect(settled).toEqual(['new'])
  })

  it('does not start an append when the gate is closed', async () => {
    const run = vi.fn()

    await withListRequest(createListRequest(), true, {
      canAppend: false,
      onAppend: () => undefined,
      onFresh: () => undefined,
      onSettle: () => undefined
    }, run)

    expect(run).not.toHaveBeenCalled()
  })
})
