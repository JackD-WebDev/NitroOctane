export default defineEventHandler(
  async (event): Promise<LogoutResponse | ErrorResponse> => {
    try {
      const response = await event.context.apiRequest('logout', {
        method: 'POST'
      });

      const cookieOptions = {
        httpOnly: false,
        secure: false,
        sameSite: 'lax' as const,
        path: '/',
        maxAge: 0
      };

      deleteCookie(event, 'NitroOctane_session', cookieOptions);
      deleteCookie(event, 'XSRF-TOKEN', cookieOptions);

      return LogoutResponseSchema.parseAsync(response);
    } catch (error) {
      const cookieOptions = {
        httpOnly: false,
        secure: false,
        sameSite: 'lax' as const,
        path: '/',
        maxAge: 0
      };

      deleteCookie(event, 'NitroOctane_session', cookieOptions);
      deleteCookie(event, 'XSRF-TOKEN', cookieOptions);

      throw error;
    }
  }
);
