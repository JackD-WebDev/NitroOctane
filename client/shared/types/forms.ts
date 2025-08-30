import { z } from 'zod';

export const createPasswordUpdateSchema = () => {
  const { t } = useI18n();
  const passwordRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/;
  return z
    .object({
      current_password: z
        .string()
        .min(1, { message: t('register.validation.password_min') }),
      password: z
        .string()
        .min(12, { message: t('register.validation.password_min') })
        .regex(passwordRegex, {
          message: t('register.validation.password_complexity')
        }),
      password_confirmation: z.string()
    })
    .refine((data) => data.password === data.password_confirmation, {
      message: t('register.validation.passwords_must_match'),
      path: ['password_confirmation']
    });
};

export const createRegisterSchema = async () => {
  const { t } = useI18n();

  const alphaDashRegex = /^[a-zA-Z0-9_-]+$/;

  const passwordRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/;

  const checkUnique = async (field: 'username' | 'email', value: string) => {
    if (!value) return false;
    try {
      const response = (await $fetch(
        `/api/check-unique?field=${field}&value=${encodeURIComponent(value)}`
      )) as unknown as UniqueCheckResponse;
      return response.unique;
    } catch {
      return false;
    }
  };
  return z.object({
    firstname: z
      .string()
      .min(2, { message: t('register.validation.firstname_min') })
      .max(50, { message: t('register.validation.firstname_max') })
      .regex(alphaDashRegex, {
        message: t('register.validation.firstname_alpha_dash')
      }),
    middlename: z
      .string()
      .max(50, { message: t('register.validation.middlename_max') })
      .regex(alphaDashRegex, {
        message: t('register.validation.middlename_alpha_dash')
      })
      .optional()
      .or(z.literal('')),
    lastname: z
      .string()
      .min(2, { message: t('register.validation.lastname_min') })
      .max(50, { message: t('register.validation.lastname_max') })
      .regex(alphaDashRegex, {
        message: t('register.validation.lastname_alpha_dash')
      }),
    username: z
      .string()
      .min(3, { message: t('register.validation.username_min') })
      .max(50, { message: t('register.validation.username_max') })
      .superRefine(async (val, ctx) => {
        if (val && !(await checkUnique('username', val))) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: t('register.validation.username_taken')
          });
        }
      }),
    email: z
      .string()
      .min(5, { message: t('register.validation.email_min') })
      .max(320, { message: t('register.validation.email_max') })
      .email({ message: t('register.validation.email_invalid') })
      .superRefine(async (val, ctx) => {
        if (val && !(await checkUnique('email', val))) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: t('register.validation.email_taken')
          });
        }
      }),
    password: z
      .string()
      .min(12, { message: t('register.validation.password_min') })
      .regex(passwordRegex, {
        message: t('register.validation.password_complexity')
      }),
    confirmPassword: z.string()
  });
};

export const createLoginSchema = () => {
  const { t } = useI18n();
  return z.object({
    email: z.string().email({ message: t('login.validation.email_invalid') }),
    password: z
      .string()
      .min(12, { message: t('login.validation.password_min') })
  });
};
