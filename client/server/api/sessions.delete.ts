export default defineEventHandler(
  async (event): Promise<LogoutOtherSessionsResponse | ErrorResponse> => {
    const { password } = await readBody(event);
    try {
      const { data: response } = await event.context.apiRequest('sessions', {
        method: 'delete',
        body: {
          password
        }
      });
      return LogoutOtherSessionsResponseSchema.parse(response);
    } catch (error) {
      event.context.error = error;
      const errorMessage = 'AN UNKNOWN ERROR OCCURRED';
      return {
        success: false,
        message: 'FAILED TO LOG OUT OTHER SESSIONS',
        errors: {
          title: 'LOGOUT ERROR',
          details: [errorMessage]
        }
      };
    }
  }
);
