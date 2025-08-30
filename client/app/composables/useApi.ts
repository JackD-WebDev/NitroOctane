export const useApi = <T = unknown>(
  endpoint: string,
  options: Partial<RequestInit> = {}
) => {
  const apiEndpoint = endpoint.startsWith('/api/')
    ? endpoint
    : `/api/${endpoint.startsWith('/') ? endpoint.slice(1) : endpoint}`;

  const makeRequest = async (retryOnCsrfError = true): Promise<T> => {
    const defaultHeaders: Record<string, string> = {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      Referer: window.location.origin,
      'X-Requested-With': 'XMLHttpRequest'
    };

    const csrfToken = useCookie('XSRF-TOKEN');
    if (csrfToken.value) {
      defaultHeaders['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken.value);
    }

    const defaults = {
      credentials: 'include' as const,
      body: null,
      headers: defaultHeaders
    };

    const { headers, method, body, ...restOptions } = {
      ...defaults,
      ...options
    };

    try {
      return (await $fetch<T>(apiEndpoint, {
        method: method as FetchMethod,
        headers: { ...defaults.headers, ...headers },
        body,
        ...restOptions
      })) as T;
    } catch (error: unknown) {
      const errorObj = error as { status?: number; statusText?: string };
      if (
        retryOnCsrfError &&
        (errorObj.status === 419 ||
          errorObj.statusText?.includes('CSRF') ||
          errorObj.statusText?.includes('token'))
      ) {
        console.log('CSRF token invalid, refreshing...');

        const xsrfCookie = useCookie('XSRF-TOKEN');
        const sessionCookie = useCookie('NitroOctane_session');
        xsrfCookie.value = null;
        sessionCookie.value = null;

        try {
          await useCsrfToken();
          return makeRequest(false);
        } catch (csrfError) {
          console.error('Failed to refresh CSRF token:', csrfError);
          throw error;
        }
      }
      throw error;
    }
  };

  return makeRequest();
};

export const useCsrfToken = async () => {
  try {
    await $fetch('/api/sanctum/csrf-cookie', {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        Referer: window.location.origin,
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const csrfCookie = useCookie('XSRF-TOKEN');
    return csrfCookie.value ? decodeURIComponent(csrfCookie.value) : null;
  } catch (error) {
    console.error('Failed to get CSRF token:', error);
    throw error;
  }
};

export const useAuthenticatedApi = async <T = unknown>(
  endpoint: string,
  options: Partial<RequestInit> = {}
) => {
  if (
    ['POST', 'PUT', 'PATCH', 'DELETE'].includes(
      (options.method || 'GET').toUpperCase()
    )
  ) {
    const existingToken = useCookie('XSRF-TOKEN');
    if (!existingToken.value) {
      try {
        console.log('Getting fresh CSRF token for', options.method, 'request');
        await useCsrfToken();
      } catch (error) {
        console.warn('Failed to get CSRF token, proceeding anyway:', error);
      }
    }
  }

  return useApi<T>(endpoint, options);
};

export const clearSessionCookies = () => {
  const xsrfCookie = useCookie('XSRF-TOKEN');
  const sessionCookie = useCookie('NitroOctane_session');

  xsrfCookie.value = null;
  sessionCookie.value = null;

  console.log('Session cookies cleared');
};
