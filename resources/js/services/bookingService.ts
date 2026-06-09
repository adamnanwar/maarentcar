import api from './api'
import type {
  Booking,
  BookingsResponse,
  BookingResponse,
  CreateBookingData,
} from '@/types/models'

export interface BookingFilters {
  status?: string
  from?: string
  to?: string
  q?: string
}

export const bookingService = {
  async create(data: CreateBookingData): Promise<{ booking: Booking; snap_token: string }> {
    const response = await api.post<BookingResponse>('/bookings', data)
    return {
      booking: response.data.booking,
      snap_token: response.data.snap_token!,
    }
  },

  async myBookings(): Promise<Booking[]> {
    const response = await api.get<BookingsResponse>('/bookings/me')
    return response.data.bookings
  },

  async get(id: string): Promise<Booking> {
    const response = await api.get<{ booking: Booking }>(`/bookings/${id}`)
    return response.data.booking
  },

  async cancel(id: string, reason?: string): Promise<Booking> {
    const response = await api.post<{ booking: Booking }>(`/bookings/${id}/cancel`, { reason })
    return response.data.booking
  },

  // Admin methods
  async listAll(filters?: BookingFilters): Promise<Booking[]> {
    const response = await api.get<BookingsResponse>('/admin/bookings', { params: filters })
    return response.data.bookings
  },

  async updateStatus(id: string, status: string, note?: string): Promise<Booking> {
    const response = await api.patch<{ booking: Booking }>(`/admin/bookings/${id}/status`, {
      status,
      note,
    })
    return response.data.booking
  },
}
