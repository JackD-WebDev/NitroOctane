import type { ErrorResponse, TwoFactorLoginResponse } from '../../shared/types';
import { TwoFactorLoginResponseSchema } from '../../shared/types/user';

export default defineEventHandler(
  async (event): Promise<TwoFactorLoginResponse | ErrorResponse> => {
    const body = await readBody(event);

    const response = await event.context.apiRequest('two-factor-challenge', {
      method: 'POST',
      body
    });

    return TwoFactorLoginResponseSchema.parseAsync(response);
  }
);
