<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class SuccessfulPasswordResetLinkRequestResponse implements SuccessfulPasswordResetLinkRequestResponseContract
{
    /**
     * Create a new response instance.
     *
     * @param  ResponseHelper  $responseHelper The response helper
     * @param  string  $status
     * @return void
     */
    public function __construct(
        protected ResponseHelper $responseHelper,
        string $status
    ) {
        $this->status = $status;
    }

    /**
     * The response status language key.
     *
     * @var string
     */
    protected $status;

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response|JsonResponse
     */
    public function toResponse($request): Response|JsonResponse
    {
        return $request->wantsJson()
            ? $this->responseHelper->requestResponse(
                [],
                trans($this->status),
                true,
                HttpResponse::HTTP_OK
            )
            : back()->with('status', trans($this->status));
    }
}
