import { z } from 'zod';

export const createRegisterSchema = () => {
  const { t } = useI18n();
  return z.object({
    firstname: z
      .string()
      .min(1, { message: t('register.validation.firstname_required') }),
    middlename: z.string().optional(),
    lastname: z
      .string()
      .min(1, { message: t('register.validation.lastname_required') }),
    username: z
      .string()
      .min(3, { message: t('register.validation.username_min') }),
    email: z
      .string()
      .email({ message: t('register.validation.email_invalid') }),
    password: z
      .string()
      .min(6, { message: t('register.validation.password_min') }),
    confirmPassword: z.string()
  });
};

export const createLoginSchema = () => {
  const { t } = useI18n();
  return z.object({
    email: z.string().email({ message: t('login.validation.email_invalid') }),
    password: z.string().min(6, { message: t('login.validation.password_min') })
  });
};
