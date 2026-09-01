import { IJsonApiResponse } from 'src/types'
import { IGalleryComment, IGalleryCommentAuthor } from 'src/types/gallery'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecord } from 'src/api/mappers/Music/helpers'

function normalizeAuthor(raw: Record<string, unknown> | null): IGalleryCommentAuthor | null {
  if (!raw) {
    return null
  }

  return {
    id: String(raw.id),
    name: String(raw.name ?? raw.email ?? 'User'),
    email: raw.email == null ? undefined : String(raw.email),
    avatar: raw.avatar == null ? null : String(raw.avatar)
  }
}

export function normalizeComment(raw: Record<string, unknown>): IGalleryComment {
  return {
    id: String(raw.id),
    content: String(raw.content ?? ''),
    commentable_id: String(raw.commentable_id ?? ''),
    commentable_type: String(raw.commentable_type ?? ''),
    created_at: String(raw.created_at ?? ''),
    user: normalizeAuthor(asRecord(raw.user))
  }
}

export function mapCommentsResponse(response: IJsonApiResponse): IGalleryComment[] {
  return mapResponse(response).map(normalizeComment)
}

export function mapCommentResponse(response: IJsonApiResponse): IGalleryComment {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Comment not found')
  }

  return normalizeComment(raw)
}
