const { apiUrl: baseURL } = useRuntimeConfig();

interface ApiRequestOptions {
  method?: FetchMethod;
  headers?: Record<string, string>;
  body?: unknown;
}

export default defineEventHandler(async (event) => {
  // Supported locales for backend. Keep in sync with nuxt.config.ts and locales/ files.
  const SUPPORTED_LOCALES = ['en_US', 'es_US', 'fr_US', 'tl_US'] as const;

  const normalizeLocale = (
    code?: string | null
  ): (typeof SUPPORTED_LOCALES)[number] | null => {
    if (!code) return null;
    let c = code.trim();
    // Replace hyphen with underscore and ensure region is uppercase
    c = c.replace('-', '_');
    if (c.includes('_')) {
      const [lang, region] = c.split('_');
      const normalized = `${lang.toLowerCase()}_${(
        region || 'US'
      ).toUpperCase()}`;
      return (SUPPORTED_LOCALES as readonly string[]).includes(normalized)
        ? (normalized as (typeof SUPPORTED_LOCALES)[number])
        : null;
    }
    // Short language only, map to *_US
    const short = `${c.toLowerCase()}_US`;
    return (SUPPORTED_LOCALES as readonly string[]).includes(short)
      ? (short as (typeof SUPPORTED_LOCALES)[number])
      : null;
  };

  const detectPreferredLocale = (): (typeof SUPPORTED_LOCALES)[number] => {
    // 1) nuxt-i18n cookie (common names)
    const cookieLang =
      getCookie(event, 'i18n_redirected') ||
      getCookie(event, 'locale') ||
      getCookie(event, 'nuxt_i18n.locale') ||
      null;
    const fromCookie = normalizeLocale(cookieLang);
    if (fromCookie) return fromCookie;

    // 2) Referer path prefix (/es_US/..., /es/..., etc.)
    const referer = getHeader(event, 'referer');
    if (referer) {
      try {
        const url = new URL(referer);
        const seg = url.pathname.split('/').filter(Boolean)[0] || '';
        const fromRef = normalizeLocale(seg);
        if (fromRef) return fromRef;
      } catch {
        // ignore invalid referer
      }
    }

    // 3) Accept-Language header (e.g., en-US,en;q=0.9)
    const accept = getHeader(event, 'accept-language');
    if (accept) {
      const primary = accept.split(',')[0]?.trim();
      const fromHeader = normalizeLocale(primary);
      if (fromHeader) return fromHeader;
    }

    // 4) Fallback
    return 'en_US';
  };

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
    const preferredLocale = detectPreferredLocale();

    const requestHeaders: Record<string, string> = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-XSRF-TOKEN': csrfToken,
      ...((options.headers as Record<string, string>) || {})
    };

    // Ensure backend receives a normalized Accept-Language header
    if (!('Accept-Language' in requestHeaders)) {
      requestHeaders['Accept-Language'] = preferredLocale;
    }

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
