export interface ApiResponse<T = unknown> {
  data: T
  message?: string
  status: 'ok' | 'error'
}

export interface PaginatedResponse<T = unknown> {
  data: T[]
  total: number
  page: number
  pageSize: number
}

export interface HealthCheck {
  status: string
  database?: string
  backend?: { status: string }
}
