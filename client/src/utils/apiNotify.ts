import axios, { AxiosError } from 'axios'
import { Notify } from 'quasar'

export function isRequestCanceled(error: unknown): boolean {
  return axios.isCancel(error) ||
    (error instanceof AxiosError && error.code === AxiosError.ERR_CANCELED)
}

export function handleApiError(error: unknown) {
  if (isRequestCanceled(error)) {
    return
  }

  let message = 'Unknown error occurred'
  if (error instanceof AxiosError) {
    const data = error.response?.data as { message?: string } | undefined

    message = data?.message || error.message
  } else if (error instanceof Error) {
    message = error.message
  }

  console.error(message)

  Notify.create({
    type: 'negative',
    message
  })
}

export function handleApiSuccess(response: unknown | void) {
  const meta = response && typeof response === 'object' && 'meta' in response
    ? (response as { meta?: { message?: string } }).meta
    : undefined
  const message = meta?.message || 'The action was done!'

  Notify.create({
    type: 'positive',
    message
  })
}
