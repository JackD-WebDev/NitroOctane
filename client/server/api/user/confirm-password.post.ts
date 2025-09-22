export default defineEventHandler(
  async (event): Promise<PasswordConfirmResponse | ErrorResponse> => {
    const body = await readBody(event);

    const acceptLanguage = getHeader(event, 'accept-language');

    const headers: Record<string, string> = {};
    if (acceptLanguage) headers['Accept-Language'] = acceptLanguage as string;

    const response = await event.context.apiRequest('user/confirm-password', {
      method: 'POST',
      body,
      headers
    });

    return PasswordConfirmResponseSchema.parseAsync(response);
  }
);
