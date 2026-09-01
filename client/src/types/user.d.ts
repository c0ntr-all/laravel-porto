export interface IUser {
  id: string
  name: string
  email: string
  first_name?: string
  last_name?: string
  patronymic?: string
  login?: string
  role?: string
  roles?: string[]
  avatar?: string | null
  created_at?: string | null
}

export interface IProfileForm {
  last_name: string
  first_name: string
  patronymic: string
  login: string
}

export interface IPasswordChangeForm {
  current_password: string
  password: string
  password_confirmation: string
}

export interface IUserUpdateDto {
  name?: string
  email?: string
}

export interface IChangePasswordDto {
  current_password: string
  password: string
  password_confirmation: string
}
