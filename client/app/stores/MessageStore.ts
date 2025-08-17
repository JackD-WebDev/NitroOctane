export const useMessageStore = defineStore('MessageStore', () => {
  const { t, locale } = useI18n();

  const status = ref<'loading' | 'failed' | 'passed' | null>('loading');
  const rawMessage = ref<string | null>(null);

  const message = computed(() => {
    void locale.value;
    if (rawMessage.value) return rawMessage.value;
    if (status.value) return t(`status.health.${status.value}`).toUpperCase();
    return '';
  });

  const setStatus = (
    newStatus: 'loading' | 'failed' | 'passed' | null
  ): void => {
    status.value = newStatus;
    if (newStatus !== null) rawMessage.value = null;
  };

  const setMessage = (newMessage: string): void => {
    rawMessage.value = newMessage;
  };

  return {
    message,
    status,
    setStatus,
    setMessage
  };
});
