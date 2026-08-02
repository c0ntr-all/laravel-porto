import { ApiRequestContext, IJsonApiResponse } from 'src/types'

export function buildCorrelationHeaders(ctx?: ApiRequestContext): Record<string, string> {
  if (!ctx?.correlationUuid) return {}

  return { 'X-Correlation-Uuid': ctx.correlationUuid }
}

export function mergeCorrelationFromResponse(
  ctx: ApiRequestContext,
  response: IJsonApiResponse
): ApiRequestContext {
  const uuid = response.meta?.correlation_uuid

  return uuid ? { ...ctx, correlationUuid: uuid } : ctx
}
