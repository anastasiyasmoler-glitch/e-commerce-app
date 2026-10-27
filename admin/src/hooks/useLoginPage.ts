import { zodResolver } from '@hookform/resolvers/zod'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { useNavigate } from 'react-router-dom'
import { isStaff } from '@/api/authApi'
import { apiErrorMessage } from '@/api/http'
import { useLogin } from '@/hooks/useLogin'
import { useLogout } from '@/hooks/useLogout'
import { useMe } from '@/hooks/useMe'
import { useSession } from '@/hooks/useSession'
import { loginSchema, type LoginValues } from '@/lib/validators/auth'
import { paths } from '@/paths'

export function useLoginPage() {
  const session = useSession()
  const me = useMe()
  const login = useLogin()
  const logout = useLogout()
  const navigate = useNavigate()
  const [error, setError] = useState<string | null>(null)

  const form = useForm<LoginValues>({ resolver: zodResolver(loginSchema) })

  const redirectToApp = Boolean(session.data && me.data && isStaff(me.data))

  async function onSubmit(values: LoginValues) {
    setError(null)
    try {
      const profile = await login.mutateAsync(values)
      if (!isStaff(profile)) {
        await logout.mutateAsync()
        setError('Admin or Analyst role required.')
        return
      }
      navigate(paths.analytics, { replace: true })
    } catch (err) {
      setError(apiErrorMessage(err))
    }
  }

  return {
    form,
    error,
    redirectToApp,
    isSubmitting: form.formState.isSubmitting,
    onSubmit,
  }
}
