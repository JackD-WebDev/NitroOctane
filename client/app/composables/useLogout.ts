export const useLogout = () => {
  const authStore = useAuthStore();
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

  return {
    handleLogout
  };
};
