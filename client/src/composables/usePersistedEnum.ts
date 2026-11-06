import { ref, watch, type Ref } from 'vue'

export function usePersistedEnum<T extends string>(
  storageKey: string,
  allowed: readonly T[],
  fallback: T
): { value: Ref<T> } {
  const read = (): T => {
    if (typeof window === 'undefined') {
      return fallback
    }

    const stored = localStorage.getItem(storageKey)
    const match = allowed.find(item => item === stored)

    return match ?? fallback
  }

  const value = ref(read()) as Ref<T>

  watch(value, (next) => {
    if (typeof window === 'undefined') {
      return
    }

    localStorage.setItem(storageKey, next)
  })

  return { value }
}
