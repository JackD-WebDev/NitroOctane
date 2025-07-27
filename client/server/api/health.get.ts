export default defineEventHandler(
  async (event): Promise<HealthResponse | ErrorResponse> => {
    try {
      const response = await event.context.apiRequest('health');
      return HealthResponseSchema.parse(response);
    } catch (error) {
      event.context.error = error;
      const errorMessage = 'AN UNKNOWN ERROR OCCURRED';
      return {
        success: false,
        message: 'FAILED TO RETRIEVE HEALTH STATUS',
        errors: {
          title: 'HEALTH CHECK ERROR',
          details: [errorMessage]
        }
      };
    }
  }
);
