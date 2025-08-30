export const useLocalizedNavigate = () => {
  const localePath = useLocalePath();

  return (to: string) => {
    return navigateTo(localePath(to));
  };
};
