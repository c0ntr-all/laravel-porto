import { IJsonApiResponse } from 'src/types'
import { IPostAttachment, IPostDocumentAttachment } from 'src/types'
import { ATTACHMENT_TYPES } from 'src/constants/LifeLog/attachment'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { isPostDocumentAttachment } from 'src/utils/document'

function normalizeDocumentAttachment(raw: Record<string, unknown>): IPostDocumentAttachment {
  return {
    id: String(raw.id),
    type: String(raw.type ?? ATTACHMENT_TYPES.document),
    attachment_type: ATTACHMENT_TYPES.document,
    attachment_id: String(raw.attachment_id ?? raw.id),
    attachment_created_at: raw.attachment_created_at == null
      ? undefined
      : String(raw.attachment_created_at),
    original_name: String(raw.original_name ?? 'Документ'),
    mime_type: String(raw.mime_type ?? ''),
    extension: String(raw.extension ?? ''),
    size: Number(raw.size ?? 0),
    download_url: String(raw.download_url ?? ''),
    created_at: raw.created_at == null ? undefined : String(raw.created_at),
    updated_at: raw.updated_at == null ? undefined : String(raw.updated_at)
  }
}

export function normalizePostAttachment(raw: Record<string, unknown>): IPostAttachment {
  const attachmentType = String(raw.attachment_type ?? '')

  if (attachmentType === ATTACHMENT_TYPES.document) {
    return normalizeDocumentAttachment(raw)
  }

  return {
    id: String(raw.id),
    type: String(raw.type ?? attachmentType),
    attachment_type: attachmentType,
    attachment_id: String(raw.attachment_id ?? raw.id),
    attachment_created_at: raw.attachment_created_at == null
      ? undefined
      : String(raw.attachment_created_at),
    description: raw.description == null ? null : String(raw.description),
    source: String(raw.source ?? ''),
    width: Number(raw.width ?? 0),
    height: Number(raw.height ?? 0),
    original_path: String(raw.original_path ?? raw.original ?? ''),
    list_thumb_path: String(raw.list_thumb_path ?? ''),
    preview_thumb_path: String(raw.preview_thumb_path ?? raw.list_thumb_path ?? ''),
    duration: raw.duration == null ? undefined : String(raw.duration)
  }
}

export function mapAttachmentsUploadResponse(response: IJsonApiResponse): IPostAttachment[] {
  return mapResponse(response).map(item => normalizePostAttachment(item))
}

export function mapPostAttachmentsResponse(items: Record<string, unknown>[]): IPostAttachment[] {
  return items.map(item => normalizePostAttachment(item))
}

export function isNormalizedPostAttachment(
  attachment: IPostAttachment
): attachment is IPostDocumentAttachment {
  return isPostDocumentAttachment(attachment)
}
