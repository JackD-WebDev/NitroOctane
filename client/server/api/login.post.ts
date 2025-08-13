export default defineEventHandler(
  async (event): Promise<LoggedInUserResponse | ErrorResponse> => {
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
    return LoggedInUserResponseSchema.parse(response);
  }
);
