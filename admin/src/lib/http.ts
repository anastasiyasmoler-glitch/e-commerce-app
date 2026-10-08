import axios from 'axios'
import { authUrl, getAccessToken } from '../auth/session'

export const http = axios.create({
  baseURL: authUrl(),
  withCredentials: true,
  headers: { Accept: 'application/json' },
})

http.interceptors.request.use((config) => {
  const token = getAccessToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})
