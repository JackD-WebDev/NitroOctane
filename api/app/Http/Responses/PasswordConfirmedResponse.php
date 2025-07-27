<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Laravel\Fortify\Fortify;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;

class PasswordConfirmedResponse implements PasswordConfirmedResponseContract
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
     * @param  Request  $request
     * @return JsonResponse|Response
     */
    public function toResponse($request): JsonResponse|Response
    {
        return $request->wantsJson()
            ? $this->responseHelper->requestResponse(
                [],
                __('auth.confirm_password.success'),
                true,
                HttpResponse::HTTP_CREATED
            )
            : redirect()->intended(Fortify::redirects('password-confirmation'));
    }
}
