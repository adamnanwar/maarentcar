import { ref, computed, readonly } from 'vue'
import { router } from '@inertiajs/vue3'
import { authService } from '@/services/authService'
import type { User, LoginData, RegisterData } from '@/types/models'

const user = ref<User | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

export function useAuth() {
  const isAuthenticated = computed(() => !!user.value || authService.isAuthenticated())
  const isAdmin = computed(() => user.value?.role === 'ADMIN')
  const isCustomer = computed(() => user.value?.role === 'CUSTOMER')

  async function login(data: LoginData): Promise<boolean> {
    loading.value = true
    error.value = null

    try {
      const response = await authService.login(data)
      authService.setToken(response.token)
      user.value = response.user
      return true
    } catch (e: unknown) {
      if (e instanceof Error) {
        error.value = e.message
      } else {
        error.value = 'Login gagal. Periksa email dan password Anda.'
      }
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(data: RegisterData): Promise<boolean> {
    loading.value = true
    error.value = null

    try {
      const response = await authService.register(data)
      authService.setToken(response.token)
      user.value = response.user
      return true
    } catch (e: unknown) {
      if (e instanceof Error) {
        error.value = e.message
      } else {
        error.value = 'Registrasi gagal. Silakan coba lagi.'
      }
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    loading.value = true

    try {
      await authService.logout()
      user.value = null
      router.visit('/')
    } catch (e) {
      // Still clear local state even if API fails
      authService.removeToken()
      user.value = null
      router.visit('/')
    } finally {
      loading.value = false
    }
  }

  async function fetchUser(): Promise<void> {
    if (!authService.isAuthenticated()) {
      return
    }

    loading.value = true

    try {
      user.value = await authService.getUser()
    } catch (e) {
      // Token is invalid
      authService.removeToken()
      user.value = null
    } finally {
      loading.value = false
    }
  }

  function setUser(userData: User | null): void {
    user.value = userData
  }

  return {
    user: readonly(user),
    loading: readonly(loading),
    error: readonly(error),
    isAuthenticated,
    isAdmin,
    isCustomer,
    login,
    register,
    logout,
    fetchUser,
    setUser,
  }
}
