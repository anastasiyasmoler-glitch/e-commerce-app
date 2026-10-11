import { clearDisplayName, writeDisplayName } from '@/auth/displayName'
import { http, setAccessToken } from './http'
import type { MeResponse } from './authApi'
import { resetSessionRestore } from './authApi'
import type { DeleteProfileValues, UpdatePasswordValues, UpdateProfileValues } from '@/lib/validators/auth'

export async function getProfile(): Promise<MeResponse> {
  const { data } = await http.get<MeResponse>('/profile')
  writeDisplayName(data.name)
  return data
}

export async function updateProfile(values: UpdateProfileValues): Promise<MeResponse> {
  const { data } = await http.patch<MeResponse>('/profile', values)
  writeDisplayName(data.name)
  return data
}

export async function updatePassword(values: UpdatePasswordValues): Promise<void> {
  await http.put('/profile/password', values)
}

export async function deleteProfile(values: DeleteProfileValues): Promise<void> {
  await http.delete('/profile', { data: values })
  setAccessToken(null)
  clearDisplayName()
  resetSessionRestore()
}
