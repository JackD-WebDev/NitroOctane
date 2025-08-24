export const useLogout = () => {
  const authStore = useAuthStore();
  const echo = useEcho();
  const { t } = useI18n();

  const handleLogout = async () => {
    try {
      await authStore.logOut();
      const localizedNavigate = useLocalizedNavigate();
      await localizedNavigate('/');
    } catch {
      const messageStore = useMessageStore();
      messageStore.setMessage(t('navbar.logout_failed'));
    }
  };

  const listenForRemoteLogout = (userId: string) => {
    const channel = echo.private(`user.${userId}`);
    channel.listen('.session.logged.out', () => {
      authStore.logOut();
      window.location.href = '/login';
    });

    if (echo.connector && 'pusher' in echo.connector && echo.connector.pusher) {
      echo.connector.pusher.connect();
    }
  };

  const stopListening = (userId: string) => {
    if (userId) {
      echo.leave(`user.${userId}`);
    }
  };

  if (import.meta.client) {
    watch(
      () => authStore.getUser?.id,
      (newId, oldId) => {
        if (oldId) {
          stopListening(oldId);
        }
        if (newId) {
          listenForRemoteLogout(newId);
        }
      },
      { immediate: true }
    );
  }

  return {
    handleLogout
  };
};
