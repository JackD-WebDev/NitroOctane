const hasStatusCode = (obj: unknown): obj is { statusCode: number } =>
  typeof obj === 'object' &&
  obj !== null &&
  'statusCode' in obj &&
  typeof (obj as Record<string, unknown>).statusCode === 'number';

export default defineEventHandler(async (event) => {
  const body = await readBody(event);
  const schema = createForgotPasswordSchema();
  const parseResult = await schema.safeParseAsync(body);
  if (!parseResult.success) {
    throw createError({
      statusCode: 422,
      statusMessage: 'VALIDATION FAILED',
      data: parseResult.error.flatten()
    });
  }

  const { email } = parseResult.data;

  const acceptLanguage = getHeader(event, 'accept-language');
  const headers: Record<string, string> = {};
  if (acceptLanguage) headers['Accept-Language'] = acceptLanguage;

  try {
    await event.context.apiRequest('forgot-password', {
      method: 'POST',
      body: { email },
      headers
    });
  } catch (error: unknown) {
    if (hasStatusCode(error) && error.statusCode >= 500) throw error;
  }

  return {
    success: true,
    message: 'PASSWORD RESET LINK SENT IF EMAIL EXISTS'
  };
});
