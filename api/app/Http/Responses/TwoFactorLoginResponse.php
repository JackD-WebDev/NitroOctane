<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Laravel\Fortify\Fortify;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * Create an instance of the response helper.
     *
     * @param  ResponseHelper  $responseHelper
     */
    public function __construct(
        protected ResponseHelper $responseHelper
    ) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function toResponse($request): JsonResponse|Response
    {
        if (!auth()->check()) {
            return $this->responseHelper->requestResponse(
                [],
                __('auth.login.fail'),
                false,
                HttpResponse::HTTP_UNAUTHORIZED
            );
        }

        $user = auth()->user();
        $username = $user->username ?? $user->name ?? 'user';
        $has2FA = ($user && isset($user->two_factor_secret) && $user->two_factor_secret) ? true : false;

        if ($user && $request->wantsJson()) {
            return $this->responseHelper->requestResponse(
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $username,
                        'preferred_language' => $user->lang,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'two_factor' => $has2FA,
                    'redirect_url' => config('app.frontend_url')
                ],
                __('auth.login.2fa.success'),
                true,
                HttpResponse::HTTP_OK
            );
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}
