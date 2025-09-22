export default defineNuxtPlugin(() => {
  // Client-only plugin: listen for verification messages from the verify tab
  if (import.meta.client) {
    const onMessage = async (ev: MessageEvent) => {
      try {
        // Only accept same-origin messages for now
        if (ev.origin !== window.location.origin) return;

        const data = ev.data as Record<string, unknown> | undefined;
        if (!data || data.type !== 'nitro:email_verified') return;

        // Refresh the auth store so the original tab picks up updated user state
        try {
          const auth = useAuthStore();
          await auth.fetchUser(true);
          await auth.refreshVerification?.();

          // Try localized navigation, then navigateTo, then fallback to window.location
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

          // Fallback: direct browser navigation
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

    // Cleanup when the window is unloading
    const onBeforeUnload = () => {
      window.removeEventListener('message', onMessage);
      window.removeEventListener('beforeunload', onBeforeUnload);
    };
    window.addEventListener('beforeunload', onBeforeUnload);
  }
});
