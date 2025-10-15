<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract;

class EmailVerificationNotificationSentResponse implements EmailVerificationNotificationSentResponseContract
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
    public function toResponse($request): Response|JsonResponse
    {
        return $request->wantsJson()
            ? $this->responseHelper->requestResponse(
                [],
                __('auth.register.verification.sent'),
                true,
                HttpResponse::HTTP_ACCEPTED
            )
            : back()->with('status', Fortify::VERIFICATION_LINK_SENT);
    }
}
