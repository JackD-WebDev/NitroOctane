export const usePasswordPush = () => {
  const authStore = useAuthStore();
  const nuxtApp = useNuxtApp();
  const { t } = useI18n();
  const echo = nuxtApp.$echo as NitroEcho;

  if (!echo) return;

  const bind = (userId: string) => {
    const channel = echo.private(`user.${userId}`);
    channel.listen('.password.changed', () => {
      const messageStore = useMessageStore();
      messageStore.setMessage(t('reset_warn').toUpperCase());
    });
    channel.listen('.session.logged.out', async () => {
      await authStore.logOut();
      const localizedNavigate = useLocalizedNavigate();
      await localizedNavigate('/login?reset=success');
    });
    channel.listen('.nitro:email_verified', async () => {
      try {
        // Small delay to ensure server has fully processed the verification
        await new Promise((resolve) => setTimeout(resolve, 500));

        // Refresh user data to get the updated email_verified_at timestamp
        await authStore.fetchUser(true);

        // Navigate to account page
        const localizedNavigate = useLocalizedNavigate();
        await localizedNavigate('/account');
      } catch (error) {
        console.error('Error handling email verification:', error);
        // Fallback to manual page reload if something goes wrong
        window.location.href = '/account';
      }
    });
  };

  const unbind = (userId: string) => {
    try {
      const anyEcho = echo as unknown as { leave?: (channel: string) => void };
      anyEcho.leave?.(`user.${userId}`);
    } catch {
      // ignore
    }
  };

  if (import.meta.client) {
    watch(
      () => authStore.user?.id as string | undefined,
      (newId, oldId) => {
        if (oldId) unbind(oldId);
        if (newId) bind(newId);
      },
      { immediate: true }
    );
  }
};
