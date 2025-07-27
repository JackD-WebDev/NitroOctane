const { apiUrl: baseURL } = useRuntimeConfig();

interface ApiRequestOptions {
  method?: FetchMethod;
  headers?: Record<string, string>;
  body?: unknown;
}

export default defineEventHandler(async (event) => {
  const ensureCsrfToken = async (): Promise<string> => {
    const existingToken = getCookie(event, 'XSRF-TOKEN');

    if (existingToken) {
      return decodeURIComponent(existingToken);
    }

    const csrfResponse = await $fetch.raw('sanctum/csrf-cookie', {
      baseURL,
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      }
    });

    let xsrfToken = '';

    const setCookieHeaders = csrfResponse.headers.getSetCookie?.() || [];

    if (setCookieHeaders.length === 0) {
      const setCookieHeader = csrfResponse.headers.get('set-cookie');
      if (setCookieHeader) {
        setCookieHeaders.push(setCookieHeader);
      }
    }

    for (const cookieHeader of setCookieHeaders) {
      if (!cookieHeader) continue;

      const [cookiePart] = cookieHeader.split(';');
      const [name, value] = cookiePart.split('=');

      if (name === 'XSRF-TOKEN') {
        xsrfToken = value;
      }

      if (name && value) {
        setCookie(event, name, value, {
          httpOnly: false,
          secure: false,
          sameSite: 'lax',
          path: '/'
        });
      }
    }

    return xsrfToken ? decodeURIComponent(xsrfToken) : '';
  };

  const apiRequest = async (
    endpoint: string,
    options: ApiRequestOptions = {}
  ) => {
    const csrfToken = await ensureCsrfToken();

    const requestHeaders: Record<string, string> = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-XSRF-TOKEN': csrfToken,
      ...((options.headers as Record<string, string>) || {})
    };

    const cookieHeader = getHeader(event, 'cookie');
    if (cookieHeader) {
      requestHeaders.Cookie = cookieHeader;
    }

    let requestBody: string | undefined;
    if (options.body) {
      requestBody =
        typeof options.body === 'string'
          ? options.body
          : JSON.stringify(options.body);
    }

    const cleanEndpoint = endpoint.startsWith('/')
      ? endpoint.slice(1)
      : endpoint;

    try {
      const response = await $fetch.raw(`${baseURL}${cleanEndpoint}`, {
        method: options.method || 'GET',
        headers: requestHeaders,
        body: requestBody
      });

      const setCookieHeaders = response.headers.getSetCookie?.() || [];

      if (setCookieHeaders.length === 0) {
        const setCookieHeader = response.headers.get('set-cookie');
        if (setCookieHeader) {
          setCookieHeaders.push(setCookieHeader);
        }
      }

      for (const cookieHeader of setCookieHeaders) {
        if (!cookieHeader) continue;

        const [cookiePart] = cookieHeader.split(';');
        const [name, value] = cookiePart.split('=');

        if (name && value) {
          setCookie(event, name, value, {
            httpOnly: false,
            secure: false,
            sameSite: 'lax',
            path: '/'
          });
        }
      }

      return response._data;
    } catch (error: unknown) {
      if (error && typeof error === 'object' && 'status' in error) {
        const fetchError = error as { status: number; data?: unknown };

        if (fetchError.status === 422 && fetchError.data) {
          return fetchError.data;
        }
      }

      throw error;
    }
  };

  event.context.apiRequest = apiRequest;
});
