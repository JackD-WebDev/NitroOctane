export const useAuthStore = defineStore(
  'AuthStore',
  () => {
    const user = ref<AuthUser | null>(null);
    const isAuthenticated = ref(false);
    const error = ref('');

    const getUser = computed(() => user.value);

    const setUser = (newUser: AuthUser | null) => {
      user.value = newUser;
      isAuthenticated.value = !!newUser;
      if (newUser) error.value = '';
    };

    const fetchUser = async () => {
      if (user.value) return;
      try {
        const response = await useApi<UserResponse>('user');
        if (response.success && response.user) {
          setUser({
            id: response.user.id,
            username: response.user.username,
            name: response.user.username,
            email: '',
            preferred_language: 'en_US'
          });
        } else {
          setUser(null);
        }
      } catch (error) {
        console.error('Failed to fetch user:', error);
        setUser(null);
      }
    };

    const logIn = async (
      credentials: { email: string; password: string },
      language?: string
    ) => {
      error.value = '';
      try {
        const response = await useApi<{
          success: boolean;
          user: AuthUser;
          message?: string;
        }>('login', {
          method: 'POST',
          body: JSON.stringify(credentials),
          headers: language ? { 'Accept-Language': language } : undefined
        });

        if (response.success && response.user) {
          setUser(response.user);
          error.value = '';
          return response.user;
        } else {
          throw new Error(response.message || 'Login failed');
        }
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Login failed';
        error.value = message;
        throw err;
      }
    };

    const register = async (newUser: NewUser, language?: string) => {
      error.value = '';
      try {
        const response = await useApi<{
          success: boolean;
          user: AuthUser;
          message?: string;
        }>('register', {
          method: 'POST',
          body: JSON.stringify(newUser),
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

    return {
      user,
      isAuthenticated,
      error,
      getUser,
      setUser,
      fetchUser,
      logIn,
      register,
      logOut
    };
  },
  {}
);
