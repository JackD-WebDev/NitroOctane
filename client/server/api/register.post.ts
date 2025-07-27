export default defineEventHandler(
  async (event): Promise<UserResponse | ErrorResponse> => {
    const body = await readBody(event);
    const response = await event.context.apiRequest('register', {
      method: 'POST',
      body
    });
    return RegisteredUserResponseSchema.parse(response);
  }
);
