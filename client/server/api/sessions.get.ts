export default defineEventHandler(
  async (event): Promise<SessionResponse | ErrorResponse> => {
    try {
      const { data: sessions } = await event.context.apiRequest('sessions');
      return SessionResponseSchema.parseAsync(sessions);
    } catch (error) {
      event.context.error = error;
      const errorMessage = 'AN UNKNOWN ERROR OCCURRED';
      return {
        success: false,
        message: 'FAILED TO RETRIEVE SESSION',
        errors: {
          title: 'SESSION ERROR',
          details: [errorMessage]
        }
      };
    }
  }
);
