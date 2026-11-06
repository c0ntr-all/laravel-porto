import { describe, expect, it } from 'vitest'
import { IJsonApiResponse } from 'src/types'
import {
  mapCommentResponse,
  normalizeComment
} from 'src/api/mappers/comment.mapper'

describe('normalizeComment', () => {
  it('stringifies fields and drops a blank updated_at', () => {
    expect(normalizeComment({
      id: 12,
      content: 'hello',
      commentable_id: 4,
      commentable_type: 'gallery',
      created_at: '2024-01-01',
      updated_at: '',
      user: {
        id: 9,
        email: 'a@b.c',
        avatar: null
      }
    })).toEqual({
      id: '12',
      content: 'hello',
      commentable_id: '4',
      commentable_type: 'gallery',
      created_at: '2024-01-01',
      updated_at: null,
      user: {
        id: '9',
        name: 'a@b.c',
        email: 'a@b.c',
        avatar: null
      }
    })
  })

  it('uses defaults when content and the author are missing', () => {
    expect(normalizeComment({ id: '1' })).toEqual({
      id: '1',
      content: '',
      commentable_id: '',
      commentable_type: '',
      created_at: '',
      updated_at: null,
      user: null
    })
  })

  it('keeps a present updated_at and falls back to User when the author has no name', () => {
    expect(normalizeComment({
      id: '2',
      updated_at: '2024-02-02',
      user: { id: '3', avatar: 'https://cdn.example/a.png' }
    })).toMatchObject({
      updated_at: '2024-02-02',
      user: {
        id: '3',
        name: 'User',
        email: undefined,
        avatar: 'https://cdn.example/a.png'
      }
    })
  })
})

describe('mapCommentResponse', () => {
  it('maps a single JSON:API comment and its included author', () => {
    const response: IJsonApiResponse = {
      data: {
        id: '1',
        type: 'comments',
        attributes: {
          content: 'hi',
          commentable_id: '4',
          commentable_type: 'media',
          created_at: '2024-01-01T00:00:00Z'
        },
        relationships: {
          user: { data: { id: '9', type: 'users' } }
        }
      },
      included: [{
        id: '9',
        type: 'users',
        attributes: {
          name: 'Ann',
          email: 'ann@example.com',
          avatar: 'https://cdn.example/a.png'
        }
      }]
    }

    expect(mapCommentResponse(response)).toEqual({
      id: '1',
      content: 'hi',
      commentable_id: '4',
      commentable_type: 'media',
      created_at: '2024-01-01T00:00:00Z',
      updated_at: null,
      user: {
        id: '9',
        name: 'Ann',
        email: 'ann@example.com',
        avatar: 'https://cdn.example/a.png'
      }
    })
  })

  it('throws when the response has no comment', () => {
    expect(() => mapCommentResponse({ data: [] })).toThrow('Comment not found')
  })
})
