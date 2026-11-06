import { computed, ref } from 'vue'

const STORAGE_KEY = 'access_token'

function readStoredToken(): string | null {
  if (typeof window === 'undefined') {
    return null
  }

  return window.localStorage.getItem(STORAGE_KEY)
}

const token = ref<string | null>(readStoredToken())
const sessionEpoch = ref(0)

let unauthorizedHandler: (() => void | Promise<void>) | null = null
let unauthorizedHandling = false

export const hasAccessToken = computed(() => Boolean(token.value))

export function getAccessToken(): string | null {
  return token.value
}

export function setAccessToken(value: string): void {
  token.value = value

  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(STORAGE_KEY, value)
}

export function clearAccessToken(): void {
  token.value = null

  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.removeItem(STORAGE_KEY)
}

export function getSessionEpoch(): number {
  return sessionEpoch.value
}

export function bumpSessionEpoch(): void {
  sessionEpoch.value += 1
}

export function setUnauthorizedHandler(handler: () => void | Promise<void>): void {
  unauthorizedHandler = handler
}

export function handleUnauthorized(): void {
  if (token.value === null) {
    return
  }

  bumpSessionEpoch()
  clearAccessToken()

  if (unauthorizedHandling || !unauthorizedHandler) {
    return
  }

  const handler = unauthorizedHandler
  unauthorizedHandling = true

  Promise.resolve(handler()).finally(() => {
    unauthorizedHandling = false
  })
}
