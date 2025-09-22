import { z } from 'zod';

const ResendVerificationSchema = z.object({
  email: z.string().email()
});

export default defineEventHandler(async (event) => {
  const body = await readBody(event);
  const parsed = await ResendVerificationSchema.safeParseAsync(body);
  if (!parsed.success) {
    throw createError({
      statusCode: 422,
      statusMessage: 'VALIDATION FAILED',
      data: parsed.error.flatten()
    });
  }

  try {
    await event.context.apiRequest('email/verification-notification', {
      method: 'POST',
      body: parsed.data
    });
  } catch (error: unknown) {
    if (typeof error === 'object' && error && 'statusCode' in error) {
      const sc = (error as { statusCode?: number }).statusCode;
      if (typeof sc === 'number' && sc >= 500) throw error;
    }
  }

  return {
    success: true,
    message: 'VERIFICATION EMAIL SENT IF ACCOUNT EXISTS'
  };
});
