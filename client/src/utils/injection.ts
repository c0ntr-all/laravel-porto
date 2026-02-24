import { InjectionKey, inject } from 'vue'

export const injectStrict = <T>(
  key: InjectionKey<T>,
  defaultValue?: T | (() => T
  ), treatDefaultAsFactory?: false): T | (() => T) => {
  const result = inject(key, defaultValue, treatDefaultAsFactory)

  if (!result) {
    throw new Error(`Could not resolve ${key.description}`)
  }

  return result
}
