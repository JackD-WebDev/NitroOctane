/*
 |--------------------------------------------------------------------------
 | Local Zod i18n helper (v4)
 |--------------------------------------------------------------------------
 |
 | This file provides a small, strongly-typed replacement for the
 | `zod-vue-i18n` v4 helper. It's intentionally conservative and only
 | implements the surface our app requires (makeZodI18nMap + zDate).
 |
 | Keep this in-repo until the upstream package fixes its ESM packaging
 | so imports like `zod-vue-i18n/v4` work reliably in our environment.
 */
import { z } from 'zod';
import { en } from 'zod/v4/locales';
import type { I18n } from 'vue-i18n';

const defaultErrorMap = en().localeError;
export const zDate = z.string().regex(/(\d{4})-\d{2}-(\d{2})/);

const PLURAL_KEYS = ['count', 'minimum', 'maximum', 'keys', 'value'] as const;

type NamedParams = Record<string, unknown>;

function retrieveCount(options?: NamedParams): number | undefined {
  if (!options) return undefined;
  for (const key of PLURAL_KEYS) {
    const v = options[key as string];
    if (typeof v === 'number') return v;
  }
  return undefined;
}

function joinValuesSafe(values?: unknown[], sep = ', '): string {
  if (!Array.isArray(values)) return '';
  return values.map((v) => String(v)).join(sep);
}

function stringifyPrimitiveSafe(v: unknown): string {
  return String(v ?? '');
}

type I18nT = (
  key: string,
  payload?: Record<string, unknown> | number,
  opts?: Record<string, unknown>
) => string;
type I18nTe = (key: string) => boolean;

function translateLabelFactory(i18n: I18n, key: string) {
  const t = i18n.global.t as unknown as I18nT;
  const te = i18n.global.te as unknown as I18nTe;

  return (
    label: string,
    {
      named = {},
      prefix,
      count
    }: { named?: NamedParams; prefix?: string; count?: number } = {}
  ) => {
    const hasCount = count ?? retrieveCount(named);
    const labelWithPrefix = prefix ? `${prefix}.${label}` : label;
    const candidateKeys = [
      `${key}.${labelWithPrefix}WithPath`,
      `${key}.${labelWithPrefix}`,
      labelWithPrefix
    ];
    const messageKey = candidateKeys.find((k) => te(k));
    if (!messageKey) return label;
    if (typeof hasCount === 'number') return t(messageKey, hasCount, { named });
    return t(messageKey, named as Record<string, unknown>);
  };
}

export type ZodIssueLike = Partial<{
  code: string;
  input: unknown;
  received: string | number | boolean | null | undefined;
  expected: string | number | boolean | null | undefined;
  values: Array<string | number | boolean>;
  maximum: number | bigint | string | Date;
  minimum: number | bigint | string | Date;
  origin: string;
  exact: boolean;
  inclusive: boolean;
  format: string;
  prefix: string;
  suffix: string;
  includes: string;
  pattern: string;
  multipleOf: number | bigint;
  keys: string[];
  params: { i18n?: string | { key?: string; options?: NamedParams } };
  path: Array<string | number>;
  type: string;
}>;

export function makeZodI18nMap(i18n: I18n, key = 'errors') {
  const d = i18n.global.d as (d: Date | number | string) => string;
  const n = i18n.global.n as (n: number | bigint | string) => string;
  const translateLabel = translateLabelFactory(i18n, key);

  return (issue: ZodIssueLike) => {
    let message = '';
    type DefaultErrorIssue = Parameters<typeof defaultErrorMap>[0];
    const defaultMessage = defaultErrorMap(issue as DefaultErrorIssue);
    if (defaultMessage) {
      if (
        typeof defaultMessage === 'object' &&
        defaultMessage !== null &&
        'message' in (defaultMessage as Record<string, unknown>)
      )
        message = (defaultMessage as { message?: string }).message ?? '';
      else message = String(defaultMessage ?? '');
    }

    const options: Record<string, unknown> & {
      named?: NamedParams;
      count?: number;
    } = {};

    switch (issue.code) {
      case 'invalid_type':
        if (issue.input === undefined) message = 'invalidTypeReceivedUndefined';
        else if (issue.input === null) message = 'invalidTypeReceivedNull';
        else {
          message = 'invalidType';
          options.named = {
            expected: translateLabel(String(issue.expected ?? ''), {
              prefix: 'types'
            }),
            received: translateLabel(String(issue.received ?? ''), {
              prefix: 'types'
            })
          };
        }
        break;

      case 'invalid_value':
        message = 'invalidValue';
        options.count = Array.isArray(issue.values)
          ? issue.values.length
          : undefined;
        options.named = {
          values: joinValuesSafe(issue.values as unknown[]),
          expected:
            Array.isArray(issue.values) && issue.values.length === 1
              ? stringifyPrimitiveSafe((issue.values as unknown[])[0])
              : joinValuesSafe(issue.values as unknown[])
        };
        break;

      case 'too_big': {
        let maximum: unknown;
        if (issue.type === 'date') maximum = d(new Date(String(issue.maximum)));
        else if (typeof issue.maximum === 'bigint') maximum = issue.maximum;
        else maximum = n(issue.maximum as unknown as number | bigint | string);
        options.count =
          typeof issue.maximum === 'bigint'
            ? undefined
            : typeof issue.maximum === 'number'
            ? issue.maximum
            : undefined;
        message = `tooBig.${issue.origin}.`;
        if (issue.exact) message += 'exact';
        else message += issue.inclusive ? 'inclusive' : 'notInclusive';
        options.named = { maximum };
        break;
      }

      case 'too_small': {
        let minimum: unknown;
        if (issue.type === 'date') minimum = d(new Date(String(issue.minimum)));
        else if (typeof issue.minimum === 'bigint') minimum = issue.minimum;
        else minimum = n(issue.minimum as unknown as number | bigint | string);
        options.count =
          typeof issue.minimum === 'bigint'
            ? undefined
            : typeof issue.minimum === 'number'
            ? issue.minimum
            : undefined;
        message = `tooSmall.${issue.origin}.`;
        if (issue.exact) message += 'exact';
        else message += issue.inclusive ? 'inclusive' : 'notInclusive';
        options.named = { minimum };
        break;
      }

      case 'invalid_format': {
        const fmt = String(issue.format ?? '');
        message = ['starts_with', 'ends_with', 'includes', 'regex'].includes(
          fmt
        )
          ? `invalidFormat.${fmt}`
          : `invalidFormat.default`;
        options.named = {
          prefix: issue.prefix,
          suffix: issue.suffix,
          includes: issue.includes,
          pattern: issue.pattern,
          format: translateLabel(fmt, { prefix: 'types' })
        };
        break;
      }

      case 'not_multiple_of':
        message = 'notMultipleOf';
        options.named = { multipleOf: issue.multipleOf };
        break;

      case 'unrecognized_keys':
        message = 'unrecognizedKeys';
        options.named = {
          keys: Array.isArray(issue.keys)
            ? joinValuesSafe(issue.keys as string[])
            : ''
        };
        break;

      case 'invalid_key':
        message = 'invalidKey';
        options.named = { origin: issue.origin };
        break;

      case 'invalid_union':
        message = 'invalidUnion';
        break;

      case 'invalid_element':
        message = 'invalidElement';
        options.named = { origin: issue.origin };
        break;

      case 'custom':
        message = 'custom';
        if (issue.params?.i18n) {
          if (typeof issue.params.i18n === 'string') {
            message = issue.params.i18n;
            break;
          }
          if (typeof issue.params.i18n === 'object' && issue.params.i18n?.key) {
            message = issue.params.i18n.key;
            if (issue.params.i18n?.options)
              options.named = issue.params.i18n.options;
          }
        }
        break;
    }

    options.named = {
      ...(options.named ?? {}),
      path: Array.isArray(issue.path)
        ? issue.path.join('.')
        : String(issue.path ?? '')
    };
    return { message: translateLabel(message, options) };
  };
}

export default { makeZodI18nMap, zDate };
