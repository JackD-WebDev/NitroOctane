export default defineEventHandler(async (event) => {
  try {
    const { user } = await event.context.apiRequest('user');
    return UserSchema.parse(user);
  } catch (error) {
    event.context.error = error;
    const errorMessage = 'An unknown error occurred';
    return {
      success: false,
      message: 'Failed to retrieve user',
      errors: {
        title: 'User Error',
        details: [errorMessage],
      },
    };
  }
});
