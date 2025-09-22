export default defineEventHandler(
  async (event): Promise<TwoFactorConfirmResponse | ErrorResponse> => {
    const body = await readBody(event);

    const acceptLanguage = getHeader(event, 'accept-language');
    const headers: Record<string, string> = {};
    if (acceptLanguage) headers['Accept-Language'] = acceptLanguage as string;

    const response = await event.context.apiRequest('user/confirmed-two-factor-authentication', {
      method: 'POST',
      body,
      headers
    });

    return TwoFactorConfirmResponseSchema.parseAsync(response);
  }
);
