export default defineEventHandler(
  async (event): Promise<TwoFactorRecoveryCodes | ErrorResponse> => {
    const acceptLanguage = getHeader(event, 'accept-language');
    const headers: Record<string, string> = {};
    if (acceptLanguage) headers['Accept-Language'] = acceptLanguage as string;

    const response = await event.context.apiRequest('user/two-factor-recovery-codes', {
      method: 'GET',
      headers
    });

    return TwoFactorRecoveryCodesSchema.parseAsync(response);
  }
);
