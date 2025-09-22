<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;

class TwoFactorConfirmedResponse implements TwoFactorConfirmedResponseContract
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
        return $request->wantsJson()
            ? $this->responseHelper->requestResponse(
                [],
                __('auth.login.2fa.confirmed'),
                true,
                HttpResponse::HTTP_OK
            )
            : back()->with('status', Fortify::TWO_FACTOR_AUTHENTICATION_CONFIRMED);
    }
}
