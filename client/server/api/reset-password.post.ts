function hasStatusCode(obj: unknown): obj is { statusCode: number } {
  return (
    typeof obj === 'object' &&
    obj !== null &&
    'statusCode' in obj &&
    typeof (obj as Record<string, unknown>).statusCode === 'number'
  );
}

export default defineEventHandler(async (event) => {
  const body = await readBody(event);
  const schema = createResetPasswordSchema();
  const parse = await schema.safeParseAsync(body);
  if (!parse.success) {
    throw createError({
      statusCode: 422,
      statusMessage: 'VALIDATION FAILED',
      data: parse.error.flatten()
    });
  }

  const { token, email, password, password_confirmation } = parse.data;

  const acceptLanguage = getHeader(event, 'accept-language');
  const headers: Record<string, string> = {};
  if (acceptLanguage) headers['Accept-Language'] = acceptLanguage;

  try {
    await event.context.apiRequest('reset-password', {
      method: 'POST',
      body: { token, email, password, password_confirmation },
      headers
    });
  } catch (e: unknown) {
    if (hasStatusCode(e) && e.statusCode >= 500) throw e;
    throw e;
  }

  return {
    success: true,
    message: 'PASSWORD RESET SUCCESSFUL',
    redirect_url: '/login?reset=success'
  };
});
