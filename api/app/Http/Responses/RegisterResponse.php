<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
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
     * @param  Request  $request  The request.
     * @return Response|JsonResponse The response.
     */
    public function toResponse($request): Response|JsonResponse
    {
        $user = $request->user() ?? [];
        $username = $user ? $user->username : 'user';

        return $this->responseHelper->requestResponse(
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'preferred_language' => $user->lang,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'redirect_url' => rtrim(config('app.frontend_url'), '/').'/login',
            ],
            __('auth.register.success', ['username' => $username]),
            true,
            HttpResponse::HTTP_CREATED
        );
    }
}
