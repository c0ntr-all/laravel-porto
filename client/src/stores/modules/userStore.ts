import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { userApi } from 'src/api/requests/userApi'
import {
  emptyUser,
  formatRole,
  mapProfile,
  toProfileUpdateDto
} from 'src/api/mappers/user.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IChangePasswordDto, IProfileForm, IUser } from 'src/types/user'

interface LoginPayload {
  email: string
  password: string
}

interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirm: string
}

const USER_STORAGE_KEY = 'home-portal.user'

function readStoredUser(): IUser {
  if (typeof window === 'undefined') {
    return emptyUser()
  }

  try {
    const raw = localStorage.getItem(USER_STORAGE_KEY)
    if (!raw) {
      return emptyUser()
    }

    return {
      ...emptyUser(),
      ...mapProfile(JSON.parse(raw))
    }
  } catch {
    return emptyUser()
  }
}

function persistUser(user: IUser): void {
  if (typeof window === 'undefined') {
    return
  }

  localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user))
}

function clearStoredUser(): void {
  if (typeof window === 'undefined') {
    return
  }

  localStorage.removeItem(USER_STORAGE_KEY)
}

function extractAccessToken(payload: unknown): string | null {
  if (!payload || typeof payload !== 'object') {
    return null
  }

  const data = payload as Record<string, unknown>
  if (typeof data.access_token === 'string') {
    return data.access_token
  }

  return null
}

export const useUserStore = defineStore('userStore', () => {
  const status = ref('')
  const message = ref('')
  const user = ref<IUser>(readStoredUser())
  const isLoading = ref(false)
  const isAvatarBusy = ref(false)

  const isLoggedIn = computed(() => Boolean(localStorage.getItem('access_token')))
  const isAdmin = computed(() => {
    const role = (user.value.role || '').toLowerCase()
    const roles = (user.value.roles || []).map(item => item.toLowerCase())
    return role === 'admin' || role === 'administrator' || roles.includes('admin')
  })
  const displayName = computed(() => {
    const parts = [user.value.last_name, user.value.first_name, user.value.patronymic]
      .map(part => (part || '').trim())
      .filter(Boolean)

    return parts.join(' ') || user.value.name || user.value.login || user.value.email || 'Пользователь'
  })
  const displayRole = computed(() => formatRole(user.value.role))
  const displayLogin = computed(() => user.value.login || user.value.email)

  function setUser(next: IUser): void {
    user.value = next
    persistUser(next)
  }

  async function fetchCurrentUser(): Promise<IUser> {
    isLoading.value = true

    try {
      const payload = await userApi.getProfile()
      const profile = mapProfile(payload)
      setUser(profile)
      return profile
    } catch (error) {
      if (!user.value.id && !user.value.email) {
        handleApiError(error)
      }
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function applySession(
    payload: unknown,
    fallback: { name: string, email: string }
  ): Promise<void> {
    const token = extractAccessToken(payload)

    if (!token) {
      throw new Error('Не удалось получить токен доступа')
    }

    localStorage.setItem('access_token', token)
    status.value = 'success'

    try {
      await fetchCurrentUser()
    } catch {
      setUser({
        ...emptyUser(),
        name: fallback.name,
        email: fallback.email,
        login: fallback.email
      })
    }
  }

  async function login(data: LoginPayload): Promise<void> {
    const payload = await userApi.login(data)
    await applySession(payload, {
      name: data.email,
      email: data.email
    })
  }

  async function logout(): Promise<void> {
    try {
      await userApi.logout()
    } catch {
      // Token is cleared locally even if the API call fails.
    } finally {
      localStorage.removeItem('access_token')
      clearStoredUser()
      status.value = ''
      user.value = emptyUser()
    }
  }

  async function register(data: RegisterPayload): Promise<boolean> {
    const payload = await userApi.register(data)

    if (!extractAccessToken(payload)) {
      return false
    }

    await applySession(payload, {
      name: data.name,
      email: data.email
    })
    return true
  }

  async function updateProfile(form: IProfileForm): Promise<IUser> {
    isLoading.value = true

    try {
      const payload = await userApi.updateProfile(toProfileUpdateDto(form))
      const profile = mapProfile(payload)
      setUser(profile)
      handleApiSuccess(payload)
      return profile
    } catch (error) {
      handleApiError(error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function changePassword(form: IChangePasswordDto): Promise<void> {
    isLoading.value = true

    try {
      const payload = await userApi.changePassword(form)
      handleApiSuccess(payload)
    } catch (error) {
      handleApiError(error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function uploadAvatar(file: File): Promise<IUser> {
    isAvatarBusy.value = true

    try {
      const payload = await userApi.uploadAvatar(file)
      const profile = mapProfile(payload)
      setUser(profile)
      handleApiSuccess(payload)
      return profile
    } catch (error) {
      handleApiError(error)
      throw error
    } finally {
      isAvatarBusy.value = false
    }
  }

  async function deleteAvatar(): Promise<IUser> {
    isAvatarBusy.value = true

    try {
      const payload = await userApi.deleteAvatar()
      const profile = mapProfile(payload)
      setUser(profile)
      handleApiSuccess(payload)
      return profile
    } catch (error) {
      handleApiError(error)
      throw error
    } finally {
      isAvatarBusy.value = false
    }
  }

  return {
    status,
    message,
    user,
    isLoading,
    isAvatarBusy,
    isLoggedIn,
    isAdmin,
    displayName,
    displayRole,
    displayLogin,
    login,
    logout,
    register,
    fetchCurrentUser,
    updateProfile,
    changePassword,
    uploadAvatar,
    deleteAvatar
  }
})
