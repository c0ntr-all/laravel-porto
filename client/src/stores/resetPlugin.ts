import { toRaw } from 'vue'
import type { PiniaPluginContext, StateTree } from 'pinia'

function isPlainRecord(value: unknown): value is Record<string, unknown> {
  if (value === null || typeof value !== 'object' || Array.isArray(value)) {
    return false
  }

  const prototype = Object.getPrototypeOf(value)
  return prototype === Object.prototype || prototype === null
}

function deepToRaw(value: unknown): unknown {
  if (value === null || typeof value !== 'object') {
    return value
  }

  const raw = toRaw(value as object)

  if (raw instanceof Set) {
    return new Set([...raw].map(item => deepToRaw(item)))
  }

  if (raw instanceof Map) {
    return new Map([...raw].map(([key, item]) => [key, deepToRaw(item)]))
  }

  if (Array.isArray(raw)) {
    return raw.map(item => deepToRaw(item))
  }

  if (!isPlainRecord(raw)) {
    return raw
  }

  const plain: Record<string, unknown> = {}

  for (const key of Object.keys(raw)) {
    plain[key] = deepToRaw(raw[key])
  }

  return plain
}

function cloneState(state: StateTree): StateTree {
  return structuredClone(deepToRaw(state)) as StateTree
}

// Setup stores keep reactive() objects in the setup closure. Replacing the
// object leaves that closure on the previous user's data, so plain objects
// are cleared in place.
function applyPlain(target: Record<string, unknown>, source: Record<string, unknown>): void {
  for (const key of Object.keys(target)) {
    if (!(key in source)) {
      delete target[key]
    }
  }

  for (const key of Object.keys(source)) {
    const next = source[key]
    const current = target[key]

    if (isPlainRecord(current) && isPlainRecord(next)) {
      applyPlain(current, next)
      continue
    }

    target[key] = next
  }
}

export function resetPlugin({ store }: PiniaPluginContext): void {
  let snapshot: StateTree

  try {
    snapshot = cloneState(store.$state)
  } catch {
    return
  }

  store.$reset = () => {
    const fresh = structuredClone(snapshot)

    store.$patch((state) => {
      for (const key of Object.keys(fresh)) {
        const current = state[key]
        const next = fresh[key]

        if (isPlainRecord(current) && isPlainRecord(next)) {
          applyPlain(current, next)
          continue
        }

        state[key] = next
      }
    })
  }
}
