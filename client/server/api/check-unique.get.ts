export default defineEventHandler(async (event) => {
  const { field, value } = getQuery(event);
  if (!field || !value) {
    return { unique: false, error: 'Missing field or value' };
  }

  const apiUrl = `check-unique?field=${field}&value=${encodeURIComponent(
    value as string
  )}`;

  try {
    const response: UniqueCheckResponse = await event.context.apiRequest(
      apiUrl,
      {
        credentials: 'include'
      }
    );
    return response;
  } catch {
    return { unique: false, error: 'API error' };
  }
});
