import { describe, expect, it } from 'vitest'
import { UserRoleEnum } from 'src/enums/User/UserRoleEnum'
import {
  emptyUser,
  formatFullName,
  getUserInitials,
  joinFullName,
  mapProfile,
  splitFullName,
  toProfileForm,
  toProfileUpdateDto
} from 'src/api/mappers/user.mapper'
import { IUser } from 'src/types/user'

function user(partial: Partial<IUser> = {}): IUser {
  return {
    ...emptyUser(),
    ...partial
  }
}

describe('splitFullName', () => {
  it('returns empty parts for blank input', () => {
    expect(splitFullName('   ')).toEqual({
      last_name: '',
      first_name: '',
      patronymic: ''
    })
  })

  it('treats a single word as the first name', () => {
    expect(splitFullName('Ivan')).toEqual({
      last_name: '',
      first_name: 'Ivan',
      patronymic: ''
    })
  })

  it('splits two words into last and first name', () => {
    expect(splitFullName('  Ivanov   Ivan  ')).toEqual({
      last_name: 'Ivanov',
      first_name: 'Ivan',
      patronymic: ''
    })
  })

  it('keeps the remaining words as the patronymic', () => {
    expect(splitFullName('Ivanov Ivan Petrovich Jr')).toEqual({
      last_name: 'Ivanov',
      first_name: 'Ivan',
      patronymic: 'Petrovich Jr'
    })
  })
})

describe('joinFullName', () => {
  it('trims parts and skips blanks', () => {
    expect(joinFullName({
      last_name: ' Ivanov ',
      first_name: 'Ivan',
      patronymic: ' '
    })).toBe('Ivanov Ivan')
  })

  it('returns an empty string when every part is blank', () => {
    expect(joinFullName({
      last_name: '',
      first_name: ' ',
      patronymic: ''
    })).toBe('')
  })
})

describe('formatFullName', () => {
  it('prefers the joined name parts', () => {
    expect(formatFullName(user({
      last_name: 'Ivanov',
      first_name: 'Ivan',
      name: 'Other',
      login: 'login',
      email: 'a@b.c'
    }))).toBe('Ivanov Ivan')
  })

  it('falls back through name, login, email, then the default label', () => {
    expect(formatFullName(user({ name: 'Display' }))).toBe('Display')
    expect(formatFullName(user({ login: 'login' }))).toBe('login')
    expect(formatFullName(user({ email: 'a@b.c' }))).toBe('a@b.c')
    expect(formatFullName(user())).toBe('Пользователь')
  })
})

describe('getUserInitials', () => {
  it('uses the first letters of last and first name', () => {
    expect(getUserInitials(user({
      last_name: 'smith',
      first_name: 'ann'
    }))).toBe('SA')
  })

  it('uses the available name part when only one is set', () => {
    expect(getUserInitials(user({ last_name: 'Smith' }))).toBe('S')
    expect(getUserInitials(user({ first_name: 'Ann' }))).toBe('A')
  })

  it('derives initials from the formatted name when parts are empty', () => {
    expect(getUserInitials(user({ name: 'Ivan Petrov' }))).toBe('IP')
    expect(getUserInitials(user({ email: 'a@b.c' }))).toBe('A')
    expect(getUserInitials(user())).toBe('П')
  })
})

describe('profile form helpers', () => {
  it('fills missing name parts from the display name and login from email', () => {
    expect(toProfileForm(user({
      name: 'Ivanov Ivan Petrovich',
      email: 'ivan@example.com'
    }))).toEqual({
      last_name: 'Ivanov',
      first_name: 'Ivan',
      patronymic: 'Petrovich',
      login: 'ivan@example.com'
    })
  })

  it('keeps explicit name parts over the display name', () => {
    expect(toProfileForm(user({
      name: 'Other Name',
      last_name: 'Petrov',
      first_name: 'Petr',
      email: 'p@example.com'
    }))).toEqual({
      last_name: 'Petrov',
      first_name: 'Petr',
      patronymic: '',
      login: 'p@example.com'
    })
  })

  it('builds an update dto from the joined name and trimmed login', () => {
    expect(toProfileUpdateDto({
      last_name: ' Ivanov ',
      first_name: 'Ivan',
      patronymic: '',
      login: '  ivan@example.com  '
    })).toEqual({
      name: 'Ivanov Ivan',
      email: 'ivan@example.com'
    })
  })

  it('uses the login as the name when the name parts are blank', () => {
    expect(toProfileUpdateDto({
      last_name: ' ',
      first_name: '',
      patronymic: '',
      login: 'login-only'
    })).toEqual({
      name: 'login-only',
      email: 'login-only'
    })
  })
})

describe('mapProfile', () => {
  it('unwraps a JSON:API resource and splits the name', () => {
    expect(mapProfile({
      data: {
        id: '7',
        attributes: {
          name: 'Ivanov Ivan Petrovich',
          email: 'ivan@example.com',
          avatar: '  https://cdn.example/a.png  ',
          role: 'admin',
          created_at: '2024-01-02'
        }
      }
    })).toEqual({
      id: '7',
      name: 'Ivanov Ivan Petrovich',
      email: 'ivan@example.com',
      first_name: 'Ivan',
      last_name: 'Ivanov',
      patronymic: 'Petrovich',
      login: 'ivan@example.com',
      role: UserRoleEnum.Admin,
      roles: [UserRoleEnum.Admin],
      avatar: 'https://cdn.example/a.png',
      created_at: '2024-01-02'
    })
  })

  it('reads a nested user and skips a placeholder avatar', () => {
    expect(mapProfile({
      user: {
        id: 3,
        name: 'Solo',
        email: 's@example.com',
        avatar: 'https://cdn.example/no-image.jpg',
        avatar_url: 'https://cdn.example/real.png',
        roles: ['user', 'administrator']
      }
    })).toMatchObject({
      id: '3',
      name: 'Solo',
      first_name: 'Solo',
      last_name: '',
      login: 's@example.com',
      role: UserRoleEnum.Default,
      roles: [UserRoleEnum.Default, UserRoleEnum.Admin],
      avatar: 'https://cdn.example/real.png',
      created_at: null
    })
  })

  it('returns an empty user for a non-object payload', () => {
    expect(mapProfile(null)).toEqual({
      id: '',
      name: '',
      email: '',
      first_name: '',
      last_name: '',
      patronymic: '',
      login: '',
      role: UserRoleEnum.Default,
      roles: [UserRoleEnum.Default],
      avatar: null,
      created_at: null
    })
  })
})
