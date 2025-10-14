export default defineNuxtPlugin(() => {
  if (import.meta.client) {
    const onMessage = async (ev: MessageEvent) => {
      try {
        if (ev.origin !== window.location.origin) return;

        const data = ev.data as Record<string, unknown> | undefined;
        if (!data || data.type !== 'nitro:email_verified') return;

        try {
          const auth = useAuthStore();
          await auth.fetchUser(true);
          await auth.refreshVerification?.();

          try {
            const localizedNavigate = useLocalizedNavigate();
            localizedNavigate('/account');
            return;
          } catch {
            // ignore
          }

          try {
            navigateTo('/account');
            return;
          } catch {
            // ignore
          }

          try {
            window.location.href = '/account';
          } catch {
            // ignore
          }
        } catch (err) {
          console.warn('[verify-message] failed to refresh auth store', err);
        }
      } catch (err) {
        console.warn('[verify-message] unexpected message handling error', err);
      }
    };

    window.addEventListener('message', onMessage);

    const onBeforeUnload = () => {
      window.removeEventListener('message', onMessage);
      window.removeEventListener('beforeunload', onBeforeUnload);
    };
    window.addEventListener('beforeunload', onBeforeUnload);
  }
});
