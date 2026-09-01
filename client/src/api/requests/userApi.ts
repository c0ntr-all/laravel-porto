import { api } from 'src/boot/axios'
import { IChangePasswordDto, IUserUpdateDto } from 'src/types/user'

export const userApi = {
  async getProfile(): Promise<unknown> {
    const response = await api.get('v1/profile')
    return response.data
  },

  async updateProfile(payload: IUserUpdateDto): Promise<unknown> {
    const response = await api.patch('v1/profile', payload)
    return response.data
  },

  async changePassword(payload: IChangePasswordDto): Promise<unknown> {
    const response = await api.put('v1/profile/password', payload)
    return response.data
  },

  async uploadAvatar(file: File): Promise<unknown> {
    const formData = new FormData()
    formData.append('file', file)
    const response = await api.post('v1/profile/avatar', formData)
    return response.data
  },

  async deleteAvatar(): Promise<unknown> {
    const response = await api.delete('v1/profile/avatar')
    return response.data
  },

  async login(payload: { email: string, password: string }): Promise<unknown> {
    const response = await api.post('v1/login', payload)
    return response.data
  },

  async logout(): Promise<unknown> {
    const response = await api.post('v1/logout')
    return response.data
  },

  async register(payload: { name: string, email: string, password: string }): Promise<unknown> {
    const response = await api.post('v1/register', payload)
    return response.data
  }
}
