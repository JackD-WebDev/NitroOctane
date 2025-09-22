export default defineEventHandler(async (event) => {
  try {
    const resp = await event.context.apiRequest('user');

    const pick = (obj: unknown, path: string[]): unknown => {
      let acc: unknown = obj;
      for (const key of path) {
        if (
          acc &&
          typeof acc === 'object' &&
          key in (acc as Record<string, unknown>)
        ) {
          acc = (acc as Record<string, unknown>)[key];
        } else {
          return undefined;
        }
      }
      return acc;
    };

    const emailVerifiedAt =
      // Common shapes we might see
      (pick(resp, ['user', 'email_verified_at']) as
        | string
        | null
        | undefined) ??
      (pick(resp, ['data', 'data', 'attributes', 'email_verified_at']) as
        | string
        | null
        | undefined) ??
      (pick(resp, ['email_verified_at']) as string | null | undefined);

    const verified = !!emailVerifiedAt;

    return {
      success: true,
      verified,
      email_verified_at: emailVerifiedAt ?? null
    };
  } catch {
    return {
      success: false,
      verified: false,
      email_verified_at: null
    };
  }
});
