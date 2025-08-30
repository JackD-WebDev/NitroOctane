import { z } from 'zod';

const TUUID = z
  .union([z.string().uuid(), z.number(), z.string()])
  .transform(String);

const TDate = z.union([z.string().datetime(), z.date()]).transform((val) => {
  if (typeof val === 'string') return val;
  return val.toISOString();
});

export const AuthUserSchema = z.object({
  id: TUUID,
  name: z.string().optional(),
  username: z.string(),
  preferred_language: z.string().default('en_US'),
  email: z.string().email(),
  created_at: TDate.optional(),
  updated_at: TDate.optional()
});

export const UserSchema = z.object({
  id: TUUID,
  username: z.string()
});

export const NewUserSchema = z.object({
  username: z.string(),
  email: z.string().email(),
  password: z.string(),
  password_confirmation: z.string()
});

export const CredentialsSchema = z.object({
  email: z.string().email(),
  password: z.string()
});

export const UserResonseSchema = z.object({
  success: z.boolean().default(true),
  message: z.string(),
  user: UserSchema
});

export const RegisteredUserResponseSchema = z.object({
  success: z.boolean().default(true),
  message: z.string(),
  user: AuthUserSchema,
  redirect_url: z.string().optional()
});

export const LoggedInUserResponseSchema = z.object({
  success: z.boolean().default(true),
  message: z.string(),
  user: AuthUserSchema,
  two_factor: z.boolean().default(false),
  redirect_url: z.string().optional()
});

export const LogoutResponseSchema = z.object({
  success: z.boolean().default(true),
  message: z.string().default('LOGGED OUT SUCCESSFULLY'),
  redirect_url: z.string().optional()
});

export const SessionResponseSchema = z.object({
  success: z.boolean().default(true),
  message: z.string(),
  data: z.array(
    z.object({
      user_agent: z.string(),
      browser: z.string(),
      platform: z.string(),
      ip: z.string(),
      isCurrentDevice: z.boolean(),
      lastActive: z.string()
    })
  )
});

export const LogoutOtherSessionsResponseSchema = z.object({
  success: z.literal(true),
  message: z.literal('OTHER BROWSER SESSIONS LOGGED OUT SUCCESSFULLY')
});

export const PasswordUpdateResponseSchema = z.object({
  success: z.boolean(),
  message: z.string(),
  errors: z.record(z.any()).optional()
});
