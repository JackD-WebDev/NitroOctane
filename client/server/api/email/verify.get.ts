import { z } from 'zod';

const VerifyEmailSchema = z.object({
  id: z.string().min(1),
  hash: z.string().min(1),
  expires: z.string().min(1),
  signature: z.string().min(1)
});

export default defineEventHandler(async (event) => {
  const query = getQuery(event);

  const parsed = VerifyEmailSchema.safeParse(query);
  if (!parsed.success) {
    throw createError({
      statusCode: 422,
      statusMessage: 'INVALID VERIFICATION PARAMETERS',
      data: parsed.error.flatten()
    });
  }

  const { id, hash, expires, signature } = parsed.data;

  try {
    const qs = new URLSearchParams({ expires, signature }).toString();
    const endpoint = `email/verify/${id}/${hash}${qs ? `?${qs}` : ''}`;

    const response = await event.context.apiRequest(endpoint, { method: 'GET' });

    return {
      success: true,
      message: 'EMAIL VERIFIED',
      data: response
    };
  } catch (error: unknown) {
    if (typeof error === 'object' && error && 'statusCode' in error) {
      const sc = (error as { statusCode?: number }).statusCode;
      if (typeof sc === 'number' && sc >= 500) throw error;
    }

    throw createError({
      statusCode: 400,
      statusMessage: 'EMAIL VERIFICATION FAILED',
      data: error
    });
  }
});
