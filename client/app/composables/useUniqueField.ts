type UniqueField = 'username' | 'email';
type UniqueCheckState = {
  loading: boolean;
  unique: boolean | null;
  message: string;
};

export function useUniqueFieldCheck(
  currentValues: Partial<Record<UniqueField, string>> = {}
) {
  const t = useI18n().t;
  const uniqueChecks = {
    username: ref<UniqueCheckState>({
      loading: false,
      unique: null,
      message: ''
    }),
    email: ref<UniqueCheckState>({ loading: false, unique: null, message: '' })
  };
  const timers: Partial<Record<UniqueField, number>> = {};
  const focused: Record<UniqueField, boolean> = {
    username: false,
    email: false
  };
  const lastInvalidValues: Partial<Record<UniqueField, string>> = {};

  const runUniquenessCheck = async (field: UniqueField, value: string) => {
    if (value === lastInvalidValues[field]) {
      uniqueChecks[field].value = {
        loading: false,
        unique: false,
        message: t(`register.validation.${field}_taken`)
      };
      return;
    }
    if (!value || value === currentValues[field]) {
      uniqueChecks[field].value = { loading: false, unique: null, message: '' };
      return;
    }
    uniqueChecks[field].value.loading = true;
    try {
      const response = await useApi<{ unique: boolean }>(
        `check-unique?field=${field}&value=${encodeURIComponent(value)}`
      );
      if (response && typeof response.unique === 'boolean') {
        uniqueChecks[field].value.unique = response.unique;
        uniqueChecks[field].value.message = response.unique
          ? ''
          : t(`register.validation.${field}_taken`);
        if (!response.unique) {
          lastInvalidValues[field] = value;
        }
      } else {
        uniqueChecks[field].value.unique = null;
        uniqueChecks[field].value.message = '';
      }
    } catch {
      uniqueChecks[field].value.message = t('register.network_error');
    } finally {
      uniqueChecks[field].value.loading = false;
    }
  };

  const scheduleUniquenessCheck = (field: UniqueField, value: string) => {
    if (timers[field]) {
      clearTimeout(timers[field]!);
    }
    timers[field] = window.setTimeout(
      () => runUniquenessCheck(field, value),
      800
    );
  };

  const onFieldInput = (field: UniqueField, val: unknown) => {
    const value = String(val || '');
    if (value !== lastInvalidValues[field]) {
      lastInvalidValues[field] = undefined;
      uniqueChecks[field].value.unique = null;
      uniqueChecks[field].value.message = '';
    }
    scheduleUniquenessCheck(field, value);
  };

  const onFieldFocus = (field: UniqueField) => {
    focused[field] = true;
  };

  const onFieldBlur = (field: UniqueField) => {
    focused[field] = false;
  };

  const resetFieldState = (field: UniqueField) => {
    uniqueChecks[field].value = { loading: false, unique: null, message: '' };
    lastInvalidValues[field] = undefined;
  };

  return {
    uniqueChecks,
    focused,
    onFieldInput,
    onFieldFocus,
    onFieldBlur,
    resetFieldState
  };
}
