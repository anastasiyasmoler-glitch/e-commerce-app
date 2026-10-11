import { zodResolver } from '@hookform/resolvers/zod'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { googleStartUrl } from '@/api/authApi'
import { apiErrorMessage } from '@/api/http'
import { Button } from '@/components/ui/button'
import { useLogin } from '@/hooks/useLogin'
import { useRegister } from '@/hooks/useRegister'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Field } from '@/components/ui/field'
import {
  loginSchema,
  registerSchema,
  type LoginValues,
  type RegisterValues,
} from '@/lib/validators/auth'

export type AuthMode = 'login' | 'register'

type AuthModalProps = {
  initialMode?: AuthMode
  onClose: () => void
}

export function AuthModal({ initialMode = 'login', onClose }: AuthModalProps) {
  const [mode, setMode] = useState<AuthMode>(initialMode)
  const loginMutation = useLogin()
  const registerMutation = useRegister()

  const loginForm = useForm<LoginValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  })
  const registerForm = useForm<RegisterValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: { name: '', phone: '', email: '', password: '' },
  })

  async function onLogin(values: LoginValues) {
    try {
      await loginMutation.mutateAsync(values)
      onClose()
    } catch {
      /* message from mutation.error */
    }
  }

  async function onRegister(values: RegisterValues) {
    try {
      await registerMutation.mutateAsync(values)
      onClose()
    } catch {
      /* message from mutation.error */
    }
  }

  const pending = mode === 'login' ? loginMutation.isPending : registerMutation.isPending
  const requestError = mode === 'login' ? loginMutation.error : registerMutation.error
  const errorText = requestError ? apiErrorMessage(requestError) : null

  return (
    <Dialog open onOpenChange={(open) => { if (!open) onClose() }}>
      <DialogContent
        className="max-w-[420px] rounded-lg p-8 sm:max-w-[420px]"
        aria-describedby={undefined}
      >
        <DialogHeader>
          <DialogTitle className="font-display text-[28px] font-medium tracking-tight">
            {mode === 'login' ? 'Sign in' : 'Create account'}
          </DialogTitle>
          <DialogDescription className="text-[14px] text-muted-foreground">
            {mode === 'login' ? 'Welcome back.' : 'Register to place orders and use the AI assistant.'}
          </DialogDescription>
        </DialogHeader>
        {errorText ? <p className="text-[13px] text-destructive">{errorText}</p> : null}
        {mode === 'login' ? (
          <form onSubmit={loginForm.handleSubmit(onLogin)}>
            <Field
              id="auth-email"
              label="Email"
              type="email"
              placeholder="you@example.com"
              autoComplete="email"
              error={loginForm.formState.errors.email?.message}
              {...loginForm.register('email')}
            />
            <Field
              id="auth-password"
              label="Password"
              type="password"
              placeholder="••••••••"
              autoComplete="current-password"
              error={loginForm.formState.errors.password?.message}
              {...loginForm.register('password')}
            />
            <Button type="submit" size="lg" className="h-[52px] w-full rounded-sm text-[16px]" disabled={pending}>
              {pending ? 'Please wait…' : 'Sign in'}
            </Button>
          </form>
        ) : (
          <form onSubmit={registerForm.handleSubmit(onRegister)}>
            <Field
              id="auth-name"
              label="Full name"
              placeholder="Anna Smith"
              autoComplete="name"
              error={registerForm.formState.errors.name?.message}
              {...registerForm.register('name')}
            />
            <Field
              id="auth-phone"
              label="Phone number"
              type="tel"
              placeholder="+1 555 123 4567"
              autoComplete="tel"
              error={registerForm.formState.errors.phone?.message}
              {...registerForm.register('phone')}
            />
            <Field
              id="auth-reg-email"
              label="Email"
              type="email"
              placeholder="you@example.com"
              autoComplete="email"
              error={registerForm.formState.errors.email?.message}
              {...registerForm.register('email')}
            />
            <Field
              id="auth-reg-password"
              label="Password"
              type="password"
              placeholder="••••••••"
              autoComplete="new-password"
              error={registerForm.formState.errors.password?.message}
              {...registerForm.register('password')}
            />
            <Button type="submit" size="lg" className="h-[52px] w-full rounded-sm text-[16px]" disabled={pending}>
              {pending ? 'Please wait…' : 'Create account'}
            </Button>
          </form>
        )}
        <Button variant="outline" size="lg" className="h-11 w-full rounded-sm" asChild>
          <a href={googleStartUrl()}>Continue with Google</a>
        </Button>
        <p className="text-center text-[13px] text-muted-foreground">
          {mode === 'login' ? (
            <>
              New to Domo?{' '}
              <button type="button" className="font-semibold text-brand" onClick={() => { setMode('register'); loginMutation.reset() }}>
                Create an account
              </button>
            </>
          ) : (
            <>
              Already have an account?{' '}
              <button type="button" className="font-semibold text-brand" onClick={() => { setMode('login'); registerMutation.reset() }}>
                Sign in
              </button>
            </>
          )}
        </p>
      </DialogContent>
    </Dialog>
  )
}
