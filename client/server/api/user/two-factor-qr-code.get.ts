export default defineEventHandler(async (event) => {
  const acceptLanguage = getHeader(event, 'accept-language');
  const headers: Record<string, string> = {};
  if (acceptLanguage) headers['Accept-Language'] = acceptLanguage as string;

  const response = await event.context.apiRequest('user/two-factor-qr-code', {
    method: 'GET',
    headers
  });

  // backend may return the raw SVG string or an object containing `svg`
  let svg: string | undefined;
  if (typeof response === 'string') {
    svg = response;
  } else if (response && typeof response === 'object' && 'svg' in response) {
    const obj = response as unknown as Record<string, unknown>;
    const maybe = obj['svg'];
    if (typeof maybe === 'string') svg = maybe;
  }

  if (!svg) {
    // fallback: return the original response as JSON if no svg found
    return response;
  }

  // return raw SVG text and set content-type so clients can treat it as SVG
  setHeader(event, 'Content-Type', 'image/svg+xml');
  // Note: returning a string from defineEventHandler will be sent as the body
  return svg;
});
