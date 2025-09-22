export default defineEventHandler(async (event) => {
  try {
    const resp = await event.context.apiRequest('user');
    const data = (
      resp && typeof resp === 'object'
        ? (resp as Record<string, unknown>)['data']
        : undefined
    ) as Record<string, unknown> | undefined;
    const resource = (
      data && typeof data === 'object'
        ? (data['data'] as Record<string, unknown> | undefined)
        : undefined
    ) as Record<string, unknown> | undefined;

    const attributes = (
      resource && typeof resource === 'object'
        ? (resource['attributes'] as Record<string, unknown> | undefined)
        : undefined
    ) as Record<string, unknown> | undefined;

    const id = String(
      (resource && 'user_id' in resource
        ? (resource['user_id'] as unknown)
        : undefined) ??
        (resp && typeof resp === 'object' && 'user' in resp
          ? (resp as { user: { id: string | number } }).user.id
          : '')
    );

    const authUserCandidate = {
      id,
      username: (attributes?.['username'] as string | undefined) ?? '',
      name: (attributes?.['name'] as string | undefined) ?? undefined,
      email: (attributes?.['email'] as string | undefined) ?? '',
      preferred_language:
        (attributes?.['preferred_language'] as string | undefined) ?? 'en_US',
      two_factor_enabled: !!(
        resource &&
        typeof resource['has2FA'] !== 'undefined' &&
        resource['has2FA']
      ),
      email_verified_at:
        (attributes?.['email_verified_at'] as string | undefined) ?? undefined
    };

    return AuthUserSchema.parseAsync(authUserCandidate);
  } catch (error) {
    event.context.error = error;
    const errorMessage = 'AN UNKNOWN ERROR OCCURRED';
    return {
      success: false,
      message: 'FAILED TO RETRIEVE USER',
      errors: {
        title: 'USER ERROR',
        details: [errorMessage]
      }
    };
  }
});
