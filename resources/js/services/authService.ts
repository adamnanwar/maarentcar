import api from './api'
import type { AuthResponse, LoginData, RegisterData, User } from '@/types/models'

export const authService = {
  async register(data: RegisterData): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/register', data)
    return response.data
  },

  async login(data: LoginData): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/login', data)
    return response.data
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout')
    localStorage.removeItem('auth_token')
  },

  async getUser(): Promise<User> {
    const response = await api.get<{ user: User }>('/auth/user')
    return response.data.user
  },

  setToken(token: string): void {
    localStorage.setItem('auth_token', token)
  },

  getToken(): string | null {
    return localStorage.getItem('auth_token')
  },

  removeToken(): void {
    localStorage.removeItem('auth_token')
  },

  isAuthenticated(): boolean {
    return !!this.getToken()
  },
}
