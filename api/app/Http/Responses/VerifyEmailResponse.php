<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Laravel\Fortify\Fortify;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;


class VerifyEmailResponse implements VerifyEmailResponseContract
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
