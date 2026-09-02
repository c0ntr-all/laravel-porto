import { IPost, IPreset } from 'src/types'
import { parsePostDate } from 'src/utils/LifeLog/post'

export interface IPresetRailBounds {
  presetId: string
  color: string
  title: string
  startPostId: string
  endPostId: string
  roundEnd: boolean
}

function resolvePresetDates(preset: IPreset): {
  start: Date | null
  end: Date | null
} {
  const startRaw = preset.date_from ?? preset.start_date ?? preset.rules?.date_from ?? null
  const endRaw = preset.date_to ?? preset.end_date ?? preset.rules?.date_to ?? null

  return {
    start: startRaw ? new Date(startRaw) : null,
    end: endRaw ? new Date(endRaw) : null
  }
}

function hasPresetDates(preset: IPreset): boolean {
  const { start, end } = resolvePresetDates(preset)
  return Boolean(start || end)
}

export function computePresetRailBounds(
  posts: IPost[],
  presets: IPreset[]
): IPresetRailBounds[] {
  if (!posts.length) {
    return []
  }

  const postsWithMeta = posts.map((post, index) => ({
    post,
    index,
    date: parsePostDate(post)
  }))

  return presets
    .filter(hasPresetDates)
    .map(preset => {
      const { start, end } = resolvePresetDates(preset)

      let startCandidates = [...postsWithMeta]
      let endCandidates = [...postsWithMeta]

      if (start) {
        startCandidates = startCandidates.filter(item => item.date >= start)
        endCandidates = endCandidates.filter(item => item.date >= start)
      }

      if (end) {
        startCandidates = startCandidates.filter(item => item.date <= end)
        endCandidates = endCandidates.filter(item => item.date <= end)
      }

      if (!startCandidates.length) {
        return null
      }

      const startPost = startCandidates.reduce((current, item) =>
        item.date < current.date ? item : current
      )

      let endPost = postsWithMeta[0]
      let roundEnd = false

      if (end) {
        if (!endCandidates.length) {
          return null
        }

        endPost = endCandidates.reduce((current, item) =>
          item.date > current.date ? item : current
        )
        roundEnd = true
      }

      if (startPost.date > endPost.date) {
        return null
      }

      return {
        presetId: preset.id,
        color: preset.color || '#90a4ae',
        title: preset.title ?? 'Preset',
        startPostId: startPost.post.id,
        endPostId: endPost.post.id,
        roundEnd
      }
    })
    .filter((item): item is IPresetRailBounds => Boolean(item))
}

export function buildPresetRailPath(
  laneX: number,
  startY: number,
  endY: number,
  width: number,
  roundEnd: boolean,
  side: 'left' | 'right' = 'right'
): string {
  const topY = Math.min(startY, endY)
  const bottomY = Math.max(startY, endY)
  const connectorX = side === 'right' ? 2 : width - 2
  const curve = Math.min(7, Math.max(4, (bottomY - topY) / 8))

  if (bottomY - topY < curve * 2) {
    return [
      `M ${connectorX} ${topY}`,
      `L ${laneX} ${topY}`,
      `L ${laneX} ${bottomY}`,
      `L ${connectorX} ${bottomY}`
    ].join(' ')
  }

  const segments = [
    `M ${connectorX} ${bottomY}`,
    `Q ${laneX} ${bottomY}, ${laneX} ${bottomY - curve}`,
    `L ${laneX} ${topY + (roundEnd ? curve : 0)}`
  ]

  if (roundEnd) {
    segments.push(`Q ${laneX} ${topY}, ${connectorX} ${topY}`)
  }

  return segments.join(' ')
}

export const PRESET_RAIL_LANE_WIDTH = 20
export const PRESET_RAILS_WIDTH = 56
