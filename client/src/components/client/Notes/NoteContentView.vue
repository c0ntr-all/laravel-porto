<template>
  <div class="note-content">
    <component
      :is="body"
      v-if="hasContent"
    />
    <p
      v-else
      class="note-content__empty"
    >
      Нет текста
    </p>
  </div>
</template>

<script lang="ts" setup>
import { computed, h, type VNode } from 'vue'
import { RouterLink } from 'vue-router'
import { INoteLink } from 'src/types'

const props = defineProps<{
  content: string | null
  wikiLinks: INoteLink[]
}>()

const emit = defineEmits<{
  'create-unresolved': [label: string]
}>()

const SAFE_TAGS = new Set([
  'a', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'font',
  'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'li',
  'ol', 'p', 'pre', 's', 'span', 'strike', 'strong', 'sub', 'sup', 'u', 'ul'
])

type NodeChild = VNode | string

const hasContent = computed(() => (props.content ?? '').trim() !== '')

const body = computed(() => renderHtml(props.content ?? '', props.wikiLinks, (label) => {
  emit('create-unresolved', label)
}))

function labelKey (value: string): string {
  return value.trim().toLocaleLowerCase()
}

function safeUrl (value: string, allowMailto: boolean): string | null {
  const url = Array.from(value).filter((char) => {
    const code = char.charCodeAt(0)
    return code > 31 && code !== 127
  }).join('').trim()
  if (!url || /\s/.test(url) || url.length > 2048) return null

  const relative = url.startsWith('#') ||
    (url.startsWith('/') && !url.startsWith('//')) ||
    url.startsWith('?')

  if (relative) {
    if (!allowMailto && (url.startsWith('#') || url.startsWith('?'))) return null
    return url
  }

  try {
    const protocol = new URL(url).protocol.replace(':', '')
    const allowed = allowMailto ? ['http', 'https', 'mailto'] : ['http', 'https']
    return allowed.includes(protocol) ? url : null
  } catch {
    return null
  }
}

function safeStyle (value: string | null): string | null {
  if (!value || /url\s*\(|expression\s*\(|javascript:/i.test(value)) return null
  return value
}

function renderLabel (label: string, links: INoteLink[], onMissing: (label: string) => void): VNode {
  const link = links.find(item => labelKey(item.label) === labelKey(label))

  if (link) {
    const title = link.name?.trim() || label
    const hint = title === label ? undefined : label

    return h(RouterLink, {
      to: `/notes/${link.id}`,
      class: 'note-wiki-link',
      title: hint
    }, {
      default: () => title
    })
  }

  return h('button', {
    type: 'button',
    class: 'note-wiki-missing',
    onClick: () => onMissing(label)
  }, label)
}

function renderText (text: string, links: INoteLink[], onMissing: (label: string) => void): NodeChild[] {
  const pattern = /\[\[([^\][\r\n]{1,255})\]\]/g
  const nodes: NodeChild[] = []
  let cursor = 0
  let match: RegExpExecArray | null

  while ((match = pattern.exec(text)) !== null) {
    const index = match.index
    if (index > cursor) nodes.push(text.slice(cursor, index))

    const label = (match[1] ?? '').trim()
    if (label) nodes.push(renderLabel(label, links, onMissing))
    else nodes.push(match[0])

    cursor = index + match[0].length
  }

  if (cursor < text.length) nodes.push(text.slice(cursor))
  return nodes
}

function renderChildren (node: Node, links: INoteLink[], onMissing: (label: string) => void): NodeChild[] {
  const children: NodeChild[] = []

  node.childNodes.forEach((child) => {
    children.push(...renderNode(child, links, onMissing))
  })

  return children
}

function renderNode (node: Node, links: INoteLink[], onMissing: (label: string) => void): NodeChild[] {
  if (node.nodeType === Node.TEXT_NODE) {
    return renderText(node.textContent ?? '', links, onMissing)
  }

  if (node.nodeType !== Node.ELEMENT_NODE) return []

  const element = node as Element
  const tag = element.tagName.toLowerCase()
  const children = renderChildren(element, links, onMissing)

  if (!SAFE_TAGS.has(tag)) return children
  if (tag === 'br' || tag === 'hr') return [h(tag)]

  if (tag === 'img') {
    const src = safeUrl(element.getAttribute('src') ?? '', false)
    if (!src) return []
    const alt = element.getAttribute('alt') ?? ''
    return [h('img', { src, alt })]
  }

  const attrs: Record<string, string> = {}
  const style = safeStyle(element.getAttribute('style'))
  if (style) attrs.style = style

  if (tag === 'a') {
    const href = safeUrl(element.getAttribute('href') ?? '', true)
    if (!href) return children
    attrs.href = href
    const target = element.getAttribute('target')
    if (target === '_blank' || target === '_self') attrs.target = target
    if (target === '_blank') attrs.rel = 'noopener noreferrer'
  }

  if (tag === 'font') {
    const color = element.getAttribute('color')
    const face = element.getAttribute('face')
    const size = element.getAttribute('size')
    if (color) attrs.color = color
    if (face) attrs.face = face
    if (size) attrs.size = size
  }

  return [h(tag, attrs, children)]
}

function renderHtml (html: string, links: INoteLink[], onMissing: (label: string) => void): VNode {
  const parsed = new DOMParser().parseFromString(html, 'text/html')
  return h('div', { class: 'note-content__html' }, renderChildren(parsed.body, links, onMissing))
}
</script>

<style lang="scss" scoped>
.note-content {
  color: #282f53;
  font-size: 15px;
  line-height: 1.55;
  word-break: break-word;

  &__empty {
    margin: 0;
    color: #9aa0b5;
  }

  :deep(p) {
    margin: 0 0 0.7em;
  }

  :deep(img) {
    max-width: 100%;
    height: auto;
  }

  :deep(.note-wiki-link) {
    color: $primary;
    font-weight: 600;
    text-decoration: none;
  }

  :deep(.note-wiki-missing) {
    padding: 0;
    border: 0;
    background: none;
    color: #9aa0b5;
    font: inherit;
    cursor: pointer;
    text-decoration: underline dotted;
  }
}
</style>
