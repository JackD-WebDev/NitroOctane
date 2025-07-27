<?php

namespace App\Http\Responses;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

class FailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function toResponse($request): JsonResponse|Response
    {
        [$key, $message] = $request->filled('recovery_code')
            ? ['recovery_code', __('auth.login.2fa.fail')]
            : ['code', __('auth.login.2fa.fail')];

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                $key => [$message],
            ]);
        }

        return redirect()->route('two-factor.login')->withErrors([$key => $message]);
    }
}
