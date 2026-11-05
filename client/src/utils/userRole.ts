import { USER_ROLES, UserRoleEnum } from 'src/enums/User/UserRoleEnum'

const rolesById = new Map(USER_ROLES.map(role => [role.id, role]))

const rolesBySlug = new Map<string, UserRoleEnum>()
for (const role of USER_ROLES) {
  rolesBySlug.set(role.slug, role.id)
  for (const alias of role.aliases ?? []) {
    rolesBySlug.set(alias, role.id)
  }
}

export function isUserRole(value: number): value is UserRoleEnum {
  return rolesById.has(value as UserRoleEnum)
}

export function userRoleLabel(role?: UserRoleEnum | null): string {
  if (role == null) {
    return rolesById.get(UserRoleEnum.Default)?.label ?? 'Пользователь'
  }

  return rolesById.get(role)?.label ?? 'Пользователь'
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}

export function resolveUserRole(value: unknown): UserRoleEnum | null {
  if (typeof value === 'number' && isUserRole(value)) {
    return value
  }

  if (typeof value === 'string') {
    const normalized = value.trim().toLowerCase()
    if (!normalized) {
      return null
    }

    if (/^\d+$/.test(normalized)) {
      const id = Number(normalized)
      return isUserRole(id) ? id : null
    }

    return rolesBySlug.get(normalized) ?? null
  }

  if (isRecord(value)) {
    return resolveUserRole(value.id) ??
      resolveUserRole(value.slug) ??
      resolveUserRole(value.name)
  }

  return null
}

function uniqueRoles(roles: UserRoleEnum[]): UserRoleEnum[] {
  const seen = new Set<UserRoleEnum>()
  const result: UserRoleEnum[] = []

  for (const role of roles) {
    if (seen.has(role)) {
      continue
    }
    seen.add(role)
    result.push(role)
  }

  return result
}

export function normalizeUserRoles(role: unknown, roles: unknown): {
  role: UserRoleEnum
  roles: UserRoleEnum[]
} {
  const fromList = Array.isArray(roles)
    ? roles.map(resolveUserRole)
    : []
  const primary = resolveUserRole(role)
  const resolved = uniqueRoles(
    [...fromList, primary].filter((item): item is UserRoleEnum => item !== null)
  )
  const nextPrimary = primary ?? resolved[0] ?? UserRoleEnum.Default

  if (!resolved.includes(nextPrimary)) {
    resolved.unshift(nextPrimary)
  }

  return {
    role: nextPrimary,
    roles: resolved
  }
}

/**
 * Empty `required` means the route is open to every authenticated user.
 * Otherwise the user needs at least one of the listed roles.
 */
export function allowsRoles(
  userRoles: readonly UserRoleEnum[],
  required?: readonly UserRoleEnum[] | null
): boolean {
  if (!required || required.length === 0) {
    return true
  }

  return required.some(role => userRoles.includes(role))
}

export function canAccessMatchedRoutes(
  userRoles: readonly UserRoleEnum[],
  matched: readonly { meta?: { roles?: readonly UserRoleEnum[] } }[]
): boolean {
  return matched.every(record => allowsRoles(userRoles, record.meta?.roles))
}
