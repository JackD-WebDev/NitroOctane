export default defineEventHandler(
  async (event): Promise<TwoFactorActivateResponse | ErrorResponse> => {
    const body = await readBody(event);

    const acceptLanguage = getHeader(event, 'accept-language');
    const headers: Record<string, string> = {};
    if (acceptLanguage) headers['Accept-Language'] = acceptLanguage as string;

    const response = await event.context.apiRequest('user/two-factor-authentication', {
      method: 'POST',
      body,
      headers
    });

    return TwoFactorActivateResponseSchema.parseAsync(response);
  }
);
