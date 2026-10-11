import type { FieldErrors } from 'react-hook-form'
import { apiErrorMessage } from '@/api/http'

export function firstFormError(errors: FieldErrors): string | null {
  for (const err of Object.values(errors)) {
    if (err && typeof err === 'object' && 'message' in err && typeof err.message === 'string') {
      return err.message
    }
  }
  return null
}

export function formErrorMessage(requestError: unknown, errors: FieldErrors): string | null {
  if (requestError) {
    return apiErrorMessage(requestError)
  }

  return firstFormError(errors)
}
