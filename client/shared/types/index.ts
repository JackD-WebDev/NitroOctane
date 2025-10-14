import { z } from 'zod';
import type { createRegisterSchema, createPasswordUpdateSchema } from './forms';
import type {
  UserSchema,
  NewUserSchema,
  AuthUserSchema,
  CredentialsSchema,
  UserResonseSchema,
  LogoutResponseSchema,
  SessionResponseSchema,
  LoggedInUserResponseSchema,
  RegisteredUserResponseSchema,
  PasswordUpdateResponseSchema,
  LogoutOtherSessionsResponseSchema,
  TwoFactorActivateResponseSchema,
  TwoFactorQRCodeSchema,
  TwoFactorRecoveryCodesSchema,
  TwoFactorConfirmResponseSchema,
  PasswordConfirmResponseSchema,
  TwoFactorDisableResponseSchema,
  TwoFactorChallengeResponseSchema,
  TwoFactorLoginResponseSchema,
  TwoFactorLoginRequestSchema,
  TwoFactorPendingResponseSchema,
  LoginResponseSchema
} from './user';

export const NitroEchoSchema = z.object({
  private: z.any()
}) as z.ZodType<{
  private: (channel: string) => {
    listen: (event: string, callback: (data: unknown) => void) => void;
  };
}>;

export const UniqueCheckResponseSchema = z.object({
  unique: z.boolean(),
  error: z.string().optional()
});

export const SupportedLocaleSchema = z.union([
  z.literal('en_US'),
  z.literal('es_US'),
  z.literal('fr_US'),
  z.literal('tl_US')
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
export type NitroEcho = z.infer<typeof NitroEchoSchema>;
export type Credentials = z.infer<typeof CredentialsSchema>;
export type FetchMethod = z.infer<typeof FetchMethodSchema>;
export type UserResponse = z.infer<typeof UserResonseSchema>;
export type ErrorResponse = z.infer<typeof ErrorResponseSchema>;
export type LoginResponse = z.infer<typeof LoginResponseSchema>;
export type LogoutResponse = z.infer<typeof LogoutResponseSchema>;
export type HealthResponse = z.infer<typeof HealthResponseSchema>;
export type SessionResponse = z.infer<typeof SessionResponseSchema>;
export type SupportedLocale = z.infer<typeof SupportedLocaleSchema>;
export type TwoFactorQRCode = z.infer<typeof TwoFactorQRCodeSchema>;
export type UniqueCheckResponse = z.infer<typeof UniqueCheckResponseSchema>;
export type LoggedInUserResponse = z.infer<typeof LoggedInUserResponseSchema>;
export type TwoFactorLoginRequest = z.infer<typeof TwoFactorLoginRequestSchema>;
export type RegisteredUserResponse = z.infer<
  typeof RegisteredUserResponseSchema
>;
export type Register = z.infer<
  Awaited<ReturnType<typeof createRegisterSchema>>
>;
export type TwoFactorLoginResponse = z.infer<
  typeof TwoFactorLoginResponseSchema
>;
export type PasswordUpdateResponse = z.infer<
  typeof PasswordUpdateResponseSchema
>;
export type TwoFactorRecoveryCodes = z.infer<
  typeof TwoFactorRecoveryCodesSchema
>;
export type PasswordConfirmResponse = z.infer<
  typeof PasswordConfirmResponseSchema
>;
export type PasswordUpdate = z.infer<
  ReturnType<typeof createPasswordUpdateSchema>
>;
export type TwoFactorConfirmResponse = z.infer<
  typeof TwoFactorConfirmResponseSchema
>;
export type TwoFactorDisableResponse = z.infer<
  typeof TwoFactorDisableResponseSchema
>;
export type TwoFactorPendingResponse = z.infer<
  typeof TwoFactorPendingResponseSchema
>;
export type TwoFactorActivateResponse = z.infer<
  typeof TwoFactorActivateResponseSchema
>;
export type TwoFactorChallengeResponse = z.infer<
  typeof TwoFactorChallengeResponseSchema
>;
export type LogoutOtherSessionsResponse = z.infer<
  typeof LogoutOtherSessionsResponseSchema
>;
