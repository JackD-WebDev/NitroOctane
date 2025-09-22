export default defineEventHandler(
  async (event): Promise<PasswordConfirmResponse | ErrorResponse> => {
    const acceptLanguage = getHeader(event, 'accept-language');

    const headers: Record<string, string> = {};
    if (acceptLanguage) headers['Accept-Language'] = acceptLanguage as string;

    const response = await event.context.apiRequest(
      'user/confirmed-password-status',
      {
        method: 'GET',
        ...headers
      }
    );

    return PasswordConfirmResponseSchema.parseAsync(response);
  }
);
