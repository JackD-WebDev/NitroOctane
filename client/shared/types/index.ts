import { z } from 'zod';
import type {
  UserSchema,
  NewUserSchema,
  AuthUserSchema,
  CredentialsSchema,
  UserResonseSchema,
  LogoutResponseSchema,
  LoggedInUserResponseSchema,
  RegisteredUserResponseSchema
} from './user';

declare module '@pinia/testing';

export const SupportedLocaleSchema = z.enum([
  'en_US',
  'es_US',
  'fr_US',
  'tl_US'
]);

export const FetchMethodSchema = z.union([
  z.literal('DELETE'),
  z.literal('GET'),
  z.literal('HEAD'),
  z.literal('PATCH'),
  z.literal('POST'),
  z.literal('PUT'),
  z.literal('CONNECT'),
  z.literal('OPTIONS'),
  z.literal('TRACE')
]);

export const HealthResponseSchema = z.object({
  success: z.boolean().default(true),
  message: z.string()
});

export const ErrorResponseSchema = z.object({
  success: z.boolean().default(false),
  message: z.string(),
  errors: z.object({
    title: z.string(),
    details: z.array(z.any())
  })
});

export type User = z.infer<typeof UserSchema>;
export type NewUser = z.infer<typeof NewUserSchema>;
export type AuthUser = z.infer<typeof AuthUserSchema>;
export type Credentials = z.infer<typeof CredentialsSchema>;
export type FetchMethod = z.infer<typeof FetchMethodSchema>;
export type UserResponse = z.infer<typeof UserResonseSchema>;
export type ErrorResponse = z.infer<typeof ErrorResponseSchema>;
export type LogoutResponse = z.infer<typeof LogoutResponseSchema>;
export type HealthResponse = z.infer<typeof HealthResponseSchema>;
export type SupportedLocale = z.infer<typeof SupportedLocaleSchema>;
export type LoggedInUserResponse = z.infer<typeof LoggedInUserResponseSchema>;
export type RegisteredUserResponse = z.infer<
  typeof RegisteredUserResponseSchema
>;
