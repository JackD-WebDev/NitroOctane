export const useAuthStore = defineStore(
  'AuthStore',
  () => {
    const user = ref<AuthUser | null>(null);
    const isAuthenticated = ref(false);
    const error = ref('');

    const getUser = computed(() => user.value);
    const isVerified = computed(() => {
      const v = (
        user.value as unknown as { email_verified_at?: string | Date } | null
      )?.email_verified_at;
      return !!v;
    });

    const setUser = (newUser: AuthUser | null) => {
      user.value = newUser;
      isAuthenticated.value = !!newUser;
      if (newUser) error.value = '';
    };

    const fetchUser = async (force = false) => {
      if (user.value && !force) return;
      try {
        // Our BFF user route returns UserSchema (id, username) currently.
        // Prefer the richer register/login responses when available; otherwise fill minimal fields.
        const response = await useApi<User | AuthUser | UserResponse>('user');
        const asUserResponse = (r: unknown): r is UserResponse =>
          !!r &&
          typeof r === 'object' &&
          'user' in (r as Record<string, unknown>);
        const asAuthUser = (r: unknown): r is AuthUser =>
          !!r &&
          typeof r === 'object' &&
          'email' in (r as Record<string, unknown>);
        const asUser = (r: unknown): r is User =>
          !!r &&
          typeof r === 'object' &&
          'id' in (r as Record<string, unknown>) &&
          'username' in (r as Record<string, unknown>);

        // Check if it's a UserResource response (has data.type === 'user')
        const asUserResource = (r: unknown): boolean =>
          !!r &&
          typeof r === 'object' &&
          'data' in (r as Record<string, unknown>) &&
          typeof (r as Record<string, unknown>).data === 'object' &&
          (r as Record<string, unknown>).data !== null &&
          'type' in
            ((r as Record<string, unknown>).data as Record<string, unknown>) &&
          ((r as Record<string, unknown>).data as Record<string, unknown>)
            .type === 'user';

        if (asUserResource(response)) {
          // Handle UserResource response structure
          const resourceData = (response as Record<string, unknown>)
            .data as Record<string, unknown>;
          const attrs = resourceData.attributes as Record<string, unknown>;
          setUser({
            id: resourceData.user_id as string,
            username: attrs.username as string,
            name: attrs.name as string,
            email: (attrs.email as string) || '',
            preferred_language: (attrs.preferred_language as string) || 'en_US',
            two_factor_enabled: (resourceData.has2FA as boolean) || false,
            email_verified_at:
              (attrs.email_verified_at as string | null) || undefined
          });
        } else if (asAuthUser(response)) {
          setUser(response);
        } else if (asUserResponse(response)) {
          // Some endpoints wrap user
          const u = response.user as unknown as Partial<AuthUser> & User;
          setUser({
            id: u.id,
            username: u.username,
            name: u.username,
            email: (u as Partial<AuthUser>).email ?? '',
            preferred_language:
              (u as Partial<AuthUser>).preferred_language ?? 'en_US',
            two_factor_enabled:
              (u as Partial<AuthUser>).two_factor_enabled ?? false,
            email_verified_at: (u as Partial<AuthUser>).email_verified_at
          });
        } else if (asUser(response)) {
          // Minimal user shape
          const u = response as User;
          setUser({
            id: u.id,
            username: u.username,
            name: u.username,
            email: '',
            preferred_language: 'en_US',
            two_factor_enabled: false
          } as AuthUser);
        } else {
          setUser(null);
        }
      } catch (error) {
        console.error('Failed to fetch user:', error);
        setUser(null);
      }
    };

    const refreshVerification = async () => {
      try {
        const response = await useApi<{
          success: boolean;
          verified: boolean;
          email_verified_at: string | null;
        }>('user/verification-status');
        if (response && response.success && user.value) {
          (
            user.value as unknown as { email_verified_at?: string | null }
          ).email_verified_at = response.email_verified_at;
        }
        return response.success ? response.verified : false;
      } catch {
        return false;
      }
    };

    const logIn = async (
      credentials: { email: string; password: string; remember?: boolean },
      language?: string
    ) => {
      error.value = '';
      try {
        // Include remember flag if provided; fall back to false
        const payload = {
          email: credentials.email,
          password: credentials.password,
          remember: !!credentials.remember
        } as Record<string, unknown>;

        const response = await useApi<LoginResponse>('login', {
          method: 'POST',
          body: JSON.stringify(payload),
          headers: language ? { 'Accept-Language': language } : undefined
        });

        const isChallenge = (
          r: unknown
        ): r is {
          success: boolean;
          two_factor_challenge: boolean;
          message?: string;
        } =>
          !!r &&
          typeof r === 'object' &&
          'two_factor_challenge' in (r as Record<string, unknown>);

        const isLoggedIn = (r: unknown): r is LoggedInUserResponse =>
          !!r &&
          typeof r === 'object' &&
          'user' in (r as Record<string, unknown>);

        const isPending = (r: unknown): r is TwoFactorPendingResponse =>
          !!r &&
          typeof r === 'object' &&
          'two_factor' in (r as Record<string, unknown>) &&
          (r as unknown as { two_factor: boolean }).two_factor === true;

        if (isChallenge(response) || isPending(response)) {
          return { requiresTwoFactor: true } as const;
        }

        if (isLoggedIn(response)) {
          if (response.two_factor) {
            return { requiresTwoFactor: true } as const;
          }
          setUser(response.user);

          // Force refresh user data to ensure we have complete info including email_verified_at
          await fetchUser(true);

          error.value = '';
          return { requiresTwoFactor: false, user: user.value } as const;
        }

        throw new Error('Login failed');
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Login failed';
        error.value = message;
        throw err;
      }
    };

    const completeTwoFactorLogin = async (
      code: string,
      recoveryCode?: string
    ) => {
      error.value = '';
      try {
        const body: TwoFactorLoginRequest = recoveryCode
          ? { code: '', recovery_code: recoveryCode }
          : { code };

        const response = await useApi<TwoFactorLoginResponse>(
          'two-factor-challenge',
          {
            method: 'POST',
            body: JSON.stringify(body)
          }
        );

        if (response.success && response.user) {
          setUser(response.user);

          // Force refresh user data to ensure we have complete info including email_verified_at
          await fetchUser(true);

          error.value = '';
          return user.value;
        } else {
          throw new Error(response.message || '2FA verification failed');
        }
      } catch (err) {
        const message =
          err instanceof Error ? err.message : '2FA verification failed';
        error.value = message;
        throw err;
      }
    };

    const register = async (newUser: NewUser, language?: string) => {
      error.value = '';
      try {
        const payload = { ...newUser } as Record<string, unknown>;
        if (language) payload.lang = language;

        const response = await useApi<{
          success: boolean;
          user: AuthUser;
          message?: string;
        }>('register', {
          method: 'POST',
          body: JSON.stringify(payload),
          headers: language ? { 'Accept-Language': language } : undefined
        });

        if (response.success && response.user) {
          setUser(response.user);
          error.value = '';
          return response.user;
        } else {
          throw new Error(response.message || 'Registration failed');
        }
      } catch (err) {
        const message =
          err instanceof Error ? err.message : 'Registration failed';
        error.value = message;
        throw err;
      }
    };

    const logOut = async () => {
      error.value = '';

      if (!isAuthenticated.value || !user.value) {
        setUser(null);
        if (import.meta.client) {
          const xsrfCookie = useCookie('XSRF-TOKEN');
          const sessionCookie = useCookie('NitroOctane_session');
          xsrfCookie.value = null;
          sessionCookie.value = null;
        }
        return;
      }

      try {
        await useApi('logout', { method: 'POST' });
        setUser(null);

        if (import.meta.client) {
          const xsrfCookie = useCookie('XSRF-TOKEN');
          const sessionCookie = useCookie('NitroOctane_session');
          xsrfCookie.value = null;
          sessionCookie.value = null;
        }
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Logout failed';
        error.value = message;
        setUser(null);
        if (import.meta.client) {
          const xsrfCookie = useCookie('XSRF-TOKEN');
          const sessionCookie = useCookie('NitroOctane_session');
          xsrfCookie.value = null;
          sessionCookie.value = null;
        }
      }
    };

    const forgotPassword = async (email: string) => {
      try {
        await useApi('forgot-password', {
          method: 'POST',
          body: JSON.stringify({ email })
        });
      } catch (err) {
        const message =
          err instanceof Error ? err.message : 'Password reset failed';
        error.value = message;
        throw err;
      }
    };

    const resetPassword = async ({
      token,
      email,
      password,
      password_confirmation
    }: {
      token: string;
      email: string;
      password: string;
      password_confirmation: string;
    }) => {
      try {
        await useApi('reset-password', {
          method: 'POST',
          body: JSON.stringify({
            token,
            email,
            password,
            password_confirmation
          })
        });
      } catch (err) {
        const message =
          err instanceof Error ? err.message : 'Password reset failed';
        error.value = message;
        throw err;
      }
    };

    return {
      user,
      isAuthenticated,
      isVerified,
      error,
      getUser,
      setUser,
      fetchUser,
      refreshVerification,
      logIn,
      completeTwoFactorLogin,
      register,
      logOut,
      forgotPassword,
      resetPassword
    };
  },
  { persist: true }
);
