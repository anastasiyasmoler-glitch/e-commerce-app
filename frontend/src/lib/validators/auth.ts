import { isValidPhoneNumber } from 'libphonenumber-js'
import { z } from 'zod'

export const loginSchema = z.object({
  email: z.email('Enter a valid email.'),
  password: z.string().min(1, 'Password is required.'),
})

export const registerSchema = z.object({
  name: z.string().min(1, 'Full name is required.'),
  phone: z
    .string()
    .min(1, 'Phone number is required.')
    .refine((value) => isValidPhoneNumber(value), 'Enter a valid phone number with country code.'),
  email: z.email('Enter a valid email.'),
  password: z
    .string()
    .min(8, 'Use at least 8 characters.')
    .regex(/[A-Za-z]/, 'Include at least one letter.')
    .regex(/[0-9]/, 'Include at least one number.'),
})

export const updateProfileSchema = registerSchema.pick({ name: true, phone: true, email: true })

export const updatePasswordSchema = z.object({
  current_password: z.string().min(1, 'Current password is required.'),
  password: registerSchema.shape.password,
  password_confirmation: z.string().min(1, 'Confirm the new password.'),
}).refine((values) => values.password === values.password_confirmation, {
  message: 'Passwords do not match.',
  path: ['password_confirmation'],
})

export const deleteProfileSchema = z.object({
  password: z.string().min(1, 'Password is required.'),
})

export type LoginValues = z.infer<typeof loginSchema>
export type RegisterValues = z.infer<typeof registerSchema>
export type UpdateProfileValues = z.infer<typeof updateProfileSchema>
export type UpdatePasswordValues = z.infer<typeof updatePasswordSchema>
export type DeleteProfileValues = z.infer<typeof deleteProfileSchema>
