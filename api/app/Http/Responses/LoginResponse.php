<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an instance of the response helper.
     *
     * @param  ResponseHelper  $responseHelper  The response helper.
     */
    public function __construct(
        protected ResponseHelper $responseHelper
    ) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse|Response
    {
        if (! auth()->check()) {
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

        // Debug remember token functionality
        $remember = $request->boolean('remember');
        if ($remember) {
            \Illuminate\Support\Facades\Log::info('LoginResponse: Remember token requested', [
                'user_id' => $user->id,
                'remember' => $remember,
                'remember_token' => $user->remember_token,
                'guard' => auth()->getDefaultDriver(),
            ]);
        }

        if ($user && $request->wantsJson()) {
            return $this->responseHelper->requestResponse(
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $username,
                        'preferred_language' => $user->lang,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'two_factor' => $has2FA,
                    'redirect_url' => config('app.frontend_url'),
                ],
                __('auth.login.success', ['username' => $username ?? 'user']),
                true,
                HttpResponse::HTTP_OK
            );
        }

        return $this->responseHelper->requestResponse(
            [
                'redirect_url' => config('app.frontend_url'),
            ],
            __('auth.login.success', ['username' => $username ?? 'user']),
            true,
            HttpResponse::HTTP_OK
        );
    }
}
