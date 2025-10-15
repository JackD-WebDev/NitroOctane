<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * Create an instance of the response helper.
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
                __('auth.verification.sent'),
                true,
                HttpResponse::HTTP_OK
            )
            : redirect()->intended(Fortify::redirects('email-verification').'?verified=1');
    }
}
