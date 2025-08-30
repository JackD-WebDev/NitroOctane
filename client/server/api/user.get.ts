export default defineEventHandler(async (event) => {
  try {
    const { user } = await event.context.apiRequest('user');
    return UserSchema.parseAsync(user);
  } catch (error) {
    event.context.error = error;
    const errorMessage = 'AN UNKNOWN ERROR OCCURRED';
    return {
      success: false,
      message: 'FAILED TO RETRIEVE USER',
      errors: {
        title: 'USER ERROR',
        details: [errorMessage]
      }
    };
  }
});
