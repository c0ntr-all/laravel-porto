import { dirname, resolve } from 'path'
import { fileURLToPath } from 'url'
import { defineConfig } from 'vitest/config'

const root = dirname(fileURLToPath(import.meta.url))

export default defineConfig({
  resolve: {
    alias: {
      src: resolve(root, 'src')
    }
  },
  test: {
    environment: 'node',
    include: ['src/**/*.spec.ts']
  }
})
