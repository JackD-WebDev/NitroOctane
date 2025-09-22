import type { TwoFactorDisableResponse } from '../../shared/types';

export const useTwoFactor = () => {
  const qrSvg = ref<string | null>(null);
  const secret = ref<string | null>(null);
  const recoveryCodes = ref<string[] | null>(null);
  const loading = ref(false);

  /**
   * Activate 2FA for the current authenticated user.
   * Calls POST /api/Activate2FA (uses useApi to ensure CSRF token)
   * Optionally sends `password` if the server requires password confirmation.
   */
  const activate = async (
    password?: string
  ): Promise<TwoFactorActivateResponse> => {
    loading.value = true;
    try {
      const body = password ? JSON.stringify({ password }) : undefined;

      const resp = await useAuthenticatedApi<TwoFactorActivateResponse>(
        'user/two-factor-authentication',
        {
          method: 'POST',
          body
        }
      );

      if (resp.svg) qrSvg.value = resp.svg;
      else if (resp.qr) qrSvg.value = resp.qr;

      if (resp.secret) secret.value = resp.secret;
      if (resp.recovery_codes) recoveryCodes.value = resp.recovery_codes;

      return resp;
    } finally {
      loading.value = false;
    }
  };

  // Confirm the user's password for sensitive actions: POST /api/user/confirm-password
  const confirmPassword = async (
    password: string
  ): Promise<PasswordConfirmResponse> => {
    const resp = await useAuthenticatedApi<PasswordConfirmResponse>(
      'user/confirm-password',
      {
        method: 'POST',
        body: JSON.stringify({ password })
      }
    );
    return resp as PasswordConfirmResponse;
  };

  // Check confirmed password status: GET /api/user/confirmed-password-status
  const confirmStatus = async (): Promise<{ confirmed: boolean }> => {
    const resp = await useApi<PasswordConfirmResponse>(
      'user/confirmed-password-status'
    );
    return { confirmed: !!(resp as PasswordConfirmResponse).success };
  };

  // Confirm the two-factor code after scanning the QR (finalize enabling)
  const confirmTwoFactor = async (
    code: string
  ): Promise<TwoFactorConfirmResponse> => {
    const resp = await useAuthenticatedApi<TwoFactorConfirmResponse>(
      'user/confirmed-two-factor-authentication',
      {
        method: 'POST',
        body: JSON.stringify({ code })
      }
    );

    // server may return recovery codes after confirming
    if ('recovery_codes' in (resp as unknown as Record<string, unknown>)) {
      const rc = (resp as unknown as { recovery_codes?: string[] })
        .recovery_codes;
      if (rc) recoveryCodes.value = rc;
    }

    return resp as TwoFactorConfirmResponse;
  };

  // Deactivate / disable two-factor authentication
  const deactivate = async (): Promise<TwoFactorConfirmResponse> => {
    const resp = await useAuthenticatedApi<TwoFactorConfirmResponse>(
      'user/two-factor-authentication',
      {
        method: 'DELETE'
      }
    );
    // clear local state on success
    if (resp && (resp as TwoFactorConfirmResponse).success) {
      qrSvg.value = null;
      secret.value = null;
      recoveryCodes.value = null;
    }
    return resp as TwoFactorConfirmResponse;
  };

  // Disable two-factor authentication (alias for deactivate)
  const disable = async (): Promise<TwoFactorDisableResponse> => {
    loading.value = true;
    try {
      const resp = await useAuthenticatedApi<TwoFactorDisableResponse>(
        'user/two-factor-authentication',
        {
          method: 'DELETE'
        }
      );

      // clear local state on success
      if (resp && resp.success) {
        qrSvg.value = null;
        secret.value = null;
        recoveryCodes.value = null;
      }

      return resp;
    } finally {
      loading.value = false;
    }
  };

  // Fetch the QR code SVG from the server
  const fetchQr = async (): Promise<void> => {
    const resp = await useAuthenticatedApi<TwoFactorQRCode>(
      'user/two-factor-qr-code'
    );
    if (resp && resp.svg) qrSvg.value = resp.svg;
    // Optionally: handle resp.url if needed
  };

  // Check if 2FA is currently enabled for the user
  const getStatus = async (): Promise<{ enabled: boolean }> => {
    try {
      const resp = await useAuthenticatedApi<Record<string, unknown>>('user');

      // Backend may return nested objects. Try common keys in order.
      const check = (o: Record<string, unknown> | undefined): boolean => {
        if (!o) return false;
        const keys = Object.keys(o);
        for (const k of keys) {
          const lk = k.toLowerCase();
          if (
            ['has2fa', 'has_2fa', 'two_factor_enabled', 'two_factor'].includes(
              lk
            )
          ) {
            const val = o[k] as unknown;
            if (typeof val === 'boolean') return val;
            if (typeof val === 'string') return val === 'true' || val === '1';
            if (typeof val === 'number') return val === 1;
          }
        }
        return false;
      };

      // try top-level
      if (check(resp as Record<string, unknown>)) return { enabled: true };

      // try nested data/data.attributes
      const respObj = resp as Record<string, unknown>;
      const data = respObj['data'] as unknown;
      if (data && typeof data === 'object') {
        const dataObj = data as Record<string, unknown>;
        if (check(dataObj)) return { enabled: true };
        const attrs = dataObj['attributes'] as unknown;
        if (attrs && typeof attrs === 'object') {
          if (check(attrs as Record<string, unknown>)) return { enabled: true };
        }
      }

      // fallback to known path: response.user.two_factor_enabled
      const dataField = respObj['data'];
      const dataAsObj =
        dataField && typeof dataField === 'object'
          ? (dataField as Record<string, unknown>)
          : undefined;
      const userCandidate =
        respObj['user'] ?? dataAsObj?.['user'] ?? dataAsObj?.['data'];
      if (userCandidate && typeof userCandidate === 'object') {
        const possible = userCandidate as Record<string, unknown>;
        if (
          'two_factor_enabled' in possible &&
          typeof possible['two_factor_enabled'] === 'boolean'
        ) {
          return { enabled: possible['two_factor_enabled'] as boolean };
        }
        if ('has2FA' in possible && typeof possible['has2FA'] === 'boolean') {
          return { enabled: possible['has2FA'] as boolean };
        }
        if ('has2fa' in possible && typeof possible['has2fa'] === 'boolean') {
          return { enabled: possible['has2fa'] as boolean };
        }
      }

      return { enabled: false };
    } catch (err) {
      console.error('Failed to check 2FA status:', err);
      return { enabled: false };
    }
  };

  // Fetch user's recovery codes
  const fetchRecoveryCodes = async (): Promise<
    TwoFactorRecoveryCodes | string[]
  > => {
    const resp = await useAuthenticatedApi<TwoFactorRecoveryCodes | string[]>(
      'user/two-factor-recovery-codes'
    );

    // Handle both array response and object response
    if (Array.isArray(resp)) {
      recoveryCodes.value = resp;
      return resp;
    } else if (resp.recovery_codes) {
      recoveryCodes.value = resp.recovery_codes;
      return resp as TwoFactorRecoveryCodes;
    }

    return resp as TwoFactorRecoveryCodes;
  };

  return {
    qrSvg,
    secret,
    recoveryCodes,
    loading,
    activate,
    confirmPassword,
    confirmStatus,
    confirmTwoFactor,
    deactivate,
    disable,
    getStatus,
    fetchQr,
    fetchRecoveryCodes
  } as const;
};

export default useTwoFactor;
