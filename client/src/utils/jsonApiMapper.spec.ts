import { describe, expect, it } from 'vitest'
import { IJsonApiResource, IJsonApiResponse } from 'src/types'
import { mapEntity, mapResponse } from 'src/utils/jsonApiMapper'

const user = (id: string, name: string): IJsonApiResource => ({
  id,
  type: 'users',
  attributes: { name }
})

describe('mapEntity', () => {
  it('flattens a single resource', () => {
    expect(mapEntity({
      id: '1',
      type: 'posts',
      attributes: { title: 'Hello' }
    })).toEqual({
      id: '1',
      type: 'posts',
      title: 'Hello'
    })
  })

  it('inlines a has-one relationship', () => {
    expect(mapEntity({
      id: '1',
      type: 'comments',
      attributes: { content: 'hi' },
      relationships: {
        user: { data: { type: 'users', id: '9' } }
      }
    }, [user('9', 'Ann')])).toEqual({
      id: '1',
      type: 'comments',
      content: 'hi',
      user: {
        id: '9',
        type: 'users',
        name: 'Ann'
      }
    })
  })

  it('inlines a has-many relationship', () => {
    expect(mapEntity({
      id: '1',
      type: 'posts',
      attributes: { title: 'Hello' },
      relationships: {
        tags: {
          data: [
            { type: 'tags', id: '1' },
            { type: 'tags', id: '2' }
          ]
        }
      }
    }, [
      { id: '1', type: 'tags', attributes: { name: 'a' } },
      { id: '2', type: 'tags', attributes: { name: 'b' } }
    ])).toMatchObject({
      tags: [
        { id: '1', type: 'tags', name: 'a' },
        { id: '2', type: 'tags', name: 'b' }
      ]
    })
  })

  it('drops relationships that are missing from included', () => {
    const entity = mapEntity({
      id: '1',
      type: 'posts',
      relationships: {
        author: { data: { type: 'users', id: '9' } },
        tags: {
          data: [
            { type: 'tags', id: '1' },
            { type: 'tags', id: '404' }
          ]
        },
        cover: { data: null }
      }
    }, [
      { id: '1', type: 'tags', attributes: { name: 'a' } }
    ])

    expect(entity.author).toBeNull()
    expect(entity.tags).toEqual([
      { id: '1', type: 'tags', name: 'a' }
    ])
    expect(entity.cover).toBeNull()
  })

  it('leaves relationships untouched when included is omitted', () => {
    expect(mapEntity({
      id: '1',
      type: 'posts',
      attributes: { title: 'Hello' },
      relationships: {
        author: { data: { type: 'users', id: '9' } }
      }
    })).toEqual({
      id: '1',
      type: 'posts',
      title: 'Hello'
    })
  })
})

describe('mapResponse', () => {
  it('maps a collection and passes included resources through', () => {
    const response: IJsonApiResponse = {
      data: [{
        id: '1',
        type: 'comments',
        attributes: { content: 'hi' },
        relationships: {
          user: { data: { type: 'users', id: '9' } }
        }
      }],
      included: [user('9', 'Ann')]
    }

    expect(mapResponse(response)).toEqual([{
      id: '1',
      type: 'comments',
      content: 'hi',
      user: { id: '9', type: 'users', name: 'Ann' }
    }])
  })

  it('wraps a single resource in a one-item list', () => {
    const response: IJsonApiResponse = {
      data: {
        id: '2',
        type: 'posts',
        attributes: { title: 'One' }
      }
    }

    expect(mapResponse(response)).toEqual([{
      id: '2',
      type: 'posts',
      title: 'One'
    }])
  })
})
