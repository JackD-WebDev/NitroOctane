export default defineEventHandler(
  async (event): Promise<LoginResponse | ErrorResponse> => {
    const body = await readBody(event);

    const acceptLanguage = getHeader(event, 'accept-language');

    const headers: Record<string, string> = {};
    if (acceptLanguage) {
      headers['Accept-Language'] = acceptLanguage;
    }

    const response = await event.context.apiRequest('login', {
      method: 'POST',
      body,
      headers
    });

    // If the backend returned HTML (for example an error page or redirect),
    // normalize it to a structured error so the client doesn't try to treat
    // the HTML string as a JSON response.
    if (typeof response === 'string') {
      const trimmed = response.trim();
      if (trimmed.startsWith('<!DOCTYPE') || trimmed.startsWith('<html')) {
        throw createError({
          statusCode: 502,
          statusMessage: 'BAD_GATEWAY',
          data: { message: 'Unexpected HTML response from API', body: trimmed }
        });
      }
    }

    const parsed = await LoginResponseSchema.safeParseAsync(response);
    if (parsed.success) return parsed.data;

    if (
      response == null ||
      (typeof response === 'string' && response.trim() === '')
    ) {
      return { success: true, two_factor_challenge: true } as LoginResponse;
    }

    throw createError({
      statusCode: 400,
      statusMessage: 'LOGIN FAILED',
      data: response
    });
  }
);
