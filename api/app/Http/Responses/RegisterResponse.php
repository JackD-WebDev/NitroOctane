<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an instance of the response helper.
     *
     * @param ResponseHelper $responseHelper The response helper.
     */
    public function __construct(
        protected ResponseHelper $responseHelper
    ) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request The request.
     * @return Response|JsonResponse The response.
     */
    public function toResponse($request): Response|JsonResponse
    {
        $user = $request->user() ?? [];
        $username = $user ? $user->username : 'user';
        
        return $this->responseHelper->requestResponse(
            [
                'user' => $user,
                'redirect_url' => rtrim(config('app.frontend_url'), '/') . '/login',
            ],
            __('auth.register.success', ['username' => $username]),
            true,
            HttpResponse::HTTP_CREATED
        );
    }
}