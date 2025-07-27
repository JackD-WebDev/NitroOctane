const messageStore = useMessageStore();
const { message } = storeToRefs(messageStore);

watch(message, (val) => {
  if (val) {
    message.value = val.toUpperCase();
  }
});

export const useAuthStore = defineStore('authStore', () => {
  const isLoggedIn = ref(false);
  const authUser = ref<AuthUser | null>(null);
  const error = ref('');

  const getAuth = computed(() => authUser.value) as Ref<AuthUser | null>;

  const logIn = async (credentials: Credentials) => {
    message.value = 'Logging in...';
    error.value = '';
    try {
      const response = await useApi<LoggedInUserResponse>('login', {
        method: 'POST',
        body: JSON.stringify(credentials)
      });

      if (response && response.success) {
        isLoggedIn.value = true;
        authUser.value = response.user;
        message.value = response.message || 'Login successful.';
        return response.user;
      } else {
        message.value = response.message || 'Login failed.';
        error.value = response.message || 'Login failed.';
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Login failed.';
      message.value = msg;
      error.value = msg;
      throw err;
    }
  };

  const logOut = async () => {
    message.value = 'Logging out...';
    error.value = '';
    try {
      await useApi('logout', { method: 'POST' });
      isLoggedIn.value = false;
      authUser.value = null;
      message.value = 'Logout successful.';
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Logout failed.';
      message.value = msg;
      error.value = msg;
      throw err;
    }
  };

  const register = async (newUser: NewUser) => {
    message.value = 'Registering...';
    error.value = '';
    try {
      const response = await useApi<{
        success: boolean;
        message: string;
        user: AuthUser;
      }>('register', {
        method: 'POST',
        body: JSON.stringify(newUser)
      });

      if (response && response.user) {
        authUser.value = response.user;
        message.value = response.message || 'Registration successful';
        isLoggedIn.value = true;
        authUser.value = response.user;
        return response.user;
      } else {
        message.value = 'Registration processed but user data not returned.';
        error.value = 'Registration processed but user data not returned.';
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Registration failed.';
      message.value = msg;
      error.value = msg;
      throw err;
    }
  };

  return {
    isLoggedIn,
    authUser,
    getAuth,
    register,
    logIn,
    logOut,
    error
  };
});
