import { IPost, IPreset } from 'src/types'
import { parsePostDate } from 'src/utils/LifeLog/post'

export interface ITimelinePostNode {
  id: string
  y: number
  title: string
  dateLabel: string
}

export interface ITimelinePresetRange {
  id: string
  title: string
  color: string
  x: number
  yStart: number
  yEnd: number
  path: string
}

export interface ITimelineLayout {
  width: number
  height: number
  trunkX: number
  nodes: ITimelinePostNode[]
  ranges: ITimelinePresetRange[]
}

const PADDING = 28
const NODE_MIN_SPACING = 44
const LANE_WIDTH = 32
const TRUNK_X = 52

function resolvePresetRange(preset: IPreset): { start: Date | null; end: Date | null } {
  const startRaw = preset.date_from ?? preset.rules?.date_from ?? null
  const endRaw = preset.date_to ?? preset.rules?.date_to ?? null

  return {
    start: startRaw ? new Date(startRaw) : null,
    end: endRaw ? new Date(endRaw) : null
  }
}

function buildBranchPath(x: number, yStart: number, yEnd: number): string {
  const curve = 18

  return [
    `M ${TRUNK_X} ${yStart}`,
    `C ${TRUNK_X + curve} ${yStart}, ${x - curve} ${yStart}, ${x} ${yStart}`,
    `L ${x} ${yEnd}`,
    `C ${x} ${yEnd + curve}, ${TRUNK_X + curve} ${yEnd}, ${TRUNK_X} ${yEnd}`
  ].join(' ')
}

export function buildTimelineLayout(posts: IPost[], presets: IPreset[]): ITimelineLayout {
  if (!posts.length) {
    return {
      width: 280,
      height: 120,
      trunkX: TRUNK_X,
      nodes: [],
      ranges: []
    }
  }

  const sortedPosts = [...posts].sort(
    (left, right) => parsePostDate(left).getTime() - parsePostDate(right).getTime()
  )

  const timestamps = sortedPosts.map(post => parsePostDate(post).getTime())
  const minTime = Math.min(...timestamps)
  const maxTime = Math.max(...timestamps)
  const timeSpan = maxTime - minTime || 1

  const contentHeight = Math.max(
    sortedPosts.length * NODE_MIN_SPACING,
    160
  )
  const height = contentHeight + PADDING * 2

  const mapTimeToY = (time: number): number =>
    PADDING + ((time - minTime) / timeSpan) * contentHeight

  const nodes: ITimelinePostNode[] = sortedPosts.map(post => {
    const date = parsePostDate(post)

    return {
      id: post.id,
      y: mapTimeToY(date.getTime()),
      title: post.title ?? 'Без названия',
      dateLabel: post.time ? `${post.date} ${post.time}` : post.date
    }
  })

  const activePresets = presets.filter(preset => {
    const range = resolvePresetRange(preset)
    return range.start || range.end
  })

  const ranges: ITimelinePresetRange[] = activePresets.map((preset, index) => {
    const range = resolvePresetRange(preset)
    const startTime = range.start?.getTime() ?? minTime
    const endTime = range.end?.getTime() ?? maxTime
    const yStart = mapTimeToY(Math.min(startTime, endTime))
    const yEnd = mapTimeToY(Math.max(startTime, endTime))
    const x = TRUNK_X + (index + 1) * LANE_WIDTH

    return {
      id: preset.id,
      title: preset.title ?? 'Preset',
      color: preset.color || '#90a4ae',
      x,
      yStart,
      yEnd,
      path: buildBranchPath(x, yStart, yEnd)
    }
  })

  const width = TRUNK_X + (activePresets.length + 1) * LANE_WIDTH + PADDING

  return {
    width,
    height,
    trunkX: TRUNK_X,
    nodes,
    ranges
  }
}
