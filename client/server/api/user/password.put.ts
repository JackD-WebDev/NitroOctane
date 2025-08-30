export default defineEventHandler(
  async (event): Promise<PasswordUpdateResponse | ErrorResponse> => {
    const body = await readBody(event);

    const acceptLanguage = getHeader(event, 'accept-language');

    const headers: Record<string, string> = {};
    if (acceptLanguage) {
      headers['Accept-Language'] = acceptLanguage;
    }

    const response = await event.context.apiRequest('user/password', {
      method: 'PUT',
      body,
      headers
    });
    return PasswordUpdateResponseSchema.parseAsync(response);
  }
);
