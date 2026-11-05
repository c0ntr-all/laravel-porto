export enum UserRoleEnum {
  Admin = 1,
  Default = 2
}

export interface UserRoleDefinition {
  id: UserRoleEnum
  slug: string
  label: string
  aliases?: readonly string[]
}

/**
 * Catalog of roles known to the client.
 * A new role is a new enum value plus one entry here.
 * `user` is the profile fallback the API sends when no role is assigned.
 */
export const USER_ROLES: readonly UserRoleDefinition[] = [
  {
    id: UserRoleEnum.Admin,
    slug: 'admin',
    label: 'Администратор',
    aliases: ['administrator']
  },
  {
    id: UserRoleEnum.Default,
    slug: 'default',
    label: 'Пользователь',
    aliases: ['user']
  }
]
