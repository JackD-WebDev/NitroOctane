export default defineEventHandler(
  async (event): Promise<LoggedInUserResponse | ErrorResponse> => {
    const body = await readBody(event);
    const response = await event.context.apiRequest('login', {
      method: 'POST',
      body
    });
    return LoggedInUserResponseSchema.parse(response);
  }
);
