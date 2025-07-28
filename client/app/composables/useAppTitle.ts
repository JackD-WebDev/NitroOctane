export const useAppTitle = (pageTitle: string) => {
  const config = useRuntimeConfig();
  const appName = config.public.applicationName || 'APP';
  const fullTitle = `${appName.toUpperCase()} | ${pageTitle.toUpperCase()}`;
  useHead({ title: fullTitle });
};
