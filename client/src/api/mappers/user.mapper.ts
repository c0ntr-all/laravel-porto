import {
  IProfileForm,
  IUser,
  IUserUpdateDto
} from 'src/types/user'

const ROLE_LABELS: Record<string, string> = {
  admin: 'Администратор',
  administrator: 'Администратор',
  user: 'Пользователь'
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}

function asString(value: unknown, fallback = ''): string {
  return typeof value === 'string' ? value : fallback
}

function asAvatar(value: unknown): string | null {
  if (typeof value !== 'string') {
    return null
  }

  const trimmed = value.trim()
  if (!trimmed || trimmed.endsWith('no-image.jpg')) {
    return null
  }

  return trimmed
}

function unwrapProfilePayload(payload: unknown): Record<string, unknown> {
  if (!isRecord(payload)) {
    return {}
  }

  if (isRecord(payload.data)) {
    const data = payload.data
    if (isRecord(data.attributes)) {
      return {
        id: data.id,
        ...data.attributes
      }
    }
    return data
  }

  if (isRecord(payload.user)) {
    return payload.user
  }

  return payload
}

export function emptyUser(): IUser {
  return {
    id: '',
    name: '',
    email: '',
    first_name: '',
    last_name: '',
    patronymic: '',
    login: '',
    role: 'user',
    roles: [],
    avatar: null,
    created_at: null
  }
}

export function splitFullName(name: string): Pick<IUser, 'last_name' | 'first_name' | 'patronymic'> {
  const parts = name.trim().split(/\s+/).filter(Boolean)

  if (parts.length === 0) {
    return { last_name: '', first_name: '', patronymic: '' }
  }

  if (parts.length === 1) {
    return { last_name: '', first_name: parts[0], patronymic: '' }
  }

  if (parts.length === 2) {
    return { last_name: parts[0], first_name: parts[1], patronymic: '' }
  }

  return {
    last_name: parts[0],
    first_name: parts[1],
    patronymic: parts.slice(2).join(' ')
  }
}

export function joinFullName(form: Pick<IProfileForm, 'last_name' | 'first_name' | 'patronymic'>): string {
  return [form.last_name, form.first_name, form.patronymic]
    .map(part => part.trim())
    .filter(Boolean)
    .join(' ')
}

export function formatFullName(user: IUser): string {
  const joined = joinFullName({
    last_name: user.last_name ?? '',
    first_name: user.first_name ?? '',
    patronymic: user.patronymic ?? ''
  })

  return joined || user.name || user.login || user.email || 'Пользователь'
}

export function formatRole(role?: string): string {
  const value = (role || 'user').toLowerCase()
  return ROLE_LABELS[value] || role || 'Пользователь'
}

export function getUserInitials(user: IUser): string {
  const first = (user.first_name || '').trim()
  const last = (user.last_name || '').trim()

  if (first || last) {
    return `${last.charAt(0)}${first.charAt(0)}`.toUpperCase() || '?'
  }

  const source = formatFullName(user)
  const parts = source.split(/\s+/).filter(Boolean)

  if (parts.length >= 2) {
    return `${parts[0].charAt(0)}${parts[1].charAt(0)}`.toUpperCase()
  }

  return source.charAt(0).toUpperCase() || '?'
}

export function toProfileForm(user: IUser): IProfileForm {
  const fromName = splitFullName(user.name || '')

  return {
    last_name: user.last_name || fromName.last_name || '',
    first_name: user.first_name || fromName.first_name || '',
    patronymic: user.patronymic || fromName.patronymic || '',
    login: user.login || user.email || ''
  }
}

export function toProfileUpdateDto(form: IProfileForm): IUserUpdateDto {
  return {
    name: joinFullName(form) || form.login,
    email: form.login.trim()
  }
}

export function mapProfile(payload: unknown): IUser {
  const raw = unwrapProfilePayload(payload)
  const name = asString(raw.name)
  const email = asString(raw.email)
  const fio = splitFullName(name)
  const roles = Array.isArray(raw.roles)
    ? raw.roles.filter((item): item is string => typeof item === 'string')
    : []

  return {
    id: String(raw.id ?? ''),
    name,
    email,
    first_name: asString(raw.first_name, fio.first_name),
    last_name: asString(raw.last_name, fio.last_name),
    patronymic: asString(raw.patronymic, fio.patronymic),
    login: asString(raw.login, email),
    role: asString(raw.role, roles[0] || 'user'),
    roles,
    avatar: asAvatar(raw.avatar) ?? asAvatar(raw.avatar_url),
    created_at: asString(raw.created_at) || null
  }
}
