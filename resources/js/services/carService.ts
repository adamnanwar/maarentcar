import api from './api'
import type { Car, CarsResponse, CarResponse, AvailabilityResponse } from '@/types/models'

export interface CarFilters {
  q?: string
  type?: string
  transmission?: string
  seats?: number
  min_price?: number
  max_price?: number
  active_only?: boolean
}

export interface CreateCarData {
  name: string
  brand?: string
  type?: string
  transmission?: string
  seats?: number
  price_per_day: number
  with_driver_price_per_day?: number
  is_active: boolean
  image?: File
}

function createCarFormData(data: CreateCarData): FormData {
  const formData = new FormData()
  formData.append('name', data.name)
  if (data.brand) formData.append('brand', data.brand)
  if (data.type) formData.append('type', data.type)
  if (data.transmission) formData.append('transmission', data.transmission)
  if (data.seats) formData.append('seats', String(data.seats))
  formData.append('price_per_day', String(data.price_per_day))
  if (data.with_driver_price_per_day) formData.append('with_driver_price_per_day', String(data.with_driver_price_per_day))
  formData.append('is_active', data.is_active ? '1' : '0')
  if (data.image) formData.append('thumbnail', data.image)
  return formData
}

export const carService = {
  async list(filters?: CarFilters): Promise<Car[]> {
    const response = await api.get<CarsResponse>('/cars', { params: filters })
    return response.data.cars
  },

  async get(id: string): Promise<Car> {
    const response = await api.get<CarResponse>(`/cars/${id}`)
    return response.data.car
  },

  async checkAvailability(
    carId: string,
    startDate: string,
    endDate: string
  ): Promise<boolean> {
    const response = await api.get<AvailabilityResponse>('/availability', {
      params: {
        car_id: carId,
        start_date: startDate,
        end_date: endDate,
      },
    })
    return response.data.available
  },

  // Admin methods
  async create(data: CreateCarData): Promise<Car> {
    const formData = createCarFormData(data)
    const response = await api.post<{ car: Car; message: string }>('/admin/cars', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data.car
  },

  async update(id: string, data: CreateCarData): Promise<Car> {
    const formData = createCarFormData(data)
    // For PATCH with FormData, we need to use POST with _method
    formData.append('_method', 'PATCH')
    const response = await api.post<{ car: Car; message: string }>(`/admin/cars/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data.car
  },

  async delete(id: string): Promise<void> {
    await api.delete(`/admin/cars/${id}`)
  },
}
