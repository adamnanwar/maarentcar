export interface User {
  id: string
  name: string
  email: string
  phone?: string
  role: 'CUSTOMER' | 'ADMIN'
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export interface Car {
  id: string
  name: string
  brand?: string
  type?: 'SUV' | 'Sedan' | 'MPV' | 'Hatchback' | 'Luxury'
  transmission?: 'Manual' | 'Automatic'
  seats?: number
  price_per_day: number
  with_driver_price_per_day?: number
  is_active: boolean
  thumbnail_url?: string
  created_at: string
  updated_at: string
}

export type BookingStatus =
  | 'PENDING_PAYMENT'
  | 'PAID'
  | 'IN_PROGRESS'
  | 'COMPLETED'
  | 'CANCELLED'
  | 'EXPIRED'

export type PaymentStatus =
  | 'PENDING'
  | 'PAID'
  | 'DENY'
  | 'CANCEL'
  | 'EXPIRE'
  | 'REFUND'

export interface Payment {
  id: string
  booking_id: string
  order_id: string
  status: PaymentStatus
  method?: string
  gross_amount: number
  midtrans_transaction_id?: string
  created_at: string
  updated_at: string
}

export interface Booking {
  id: string
  order_id: string
  user_id: string
  car_id: string
  start_date: string
  end_date: string
  pickup_location?: string
  use_driver: boolean
  notes?: string
  total_price: number
  status: BookingStatus
  payment_deadline: string
  created_at: string
  updated_at: string
  // Relations
  car?: Car
  user?: User
  payment?: Payment
}

export interface ApiResponse<T> {
  message?: string
  data?: T
}

export interface CarsResponse {
  cars: Car[]
}

export interface CarResponse {
  car: Car
}

export interface BookingsResponse {
  bookings: Booking[]
}

export interface BookingResponse {
  booking: Booking
  snap_token?: string
}

export interface AuthResponse {
  message: string
  user: User
  token: string
}

export interface AvailabilityResponse {
  available: boolean
}

export interface CreateBookingData {
  car_id: string
  start_date: string
  end_date: string
  pickup_location?: string
  use_driver: boolean
  notes?: string
}

export interface LoginData {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  phone?: string
  password: string
  password_confirmation: string
}
