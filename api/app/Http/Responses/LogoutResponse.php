<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
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
     * @return JsonResponse|Response The response.
     */
    public function toResponse($request): JsonResponse|Response
    {
        return $this->responseHelper->requestResponse(
            [
                'redirect_url' => rtrim(config('app.frontend_url'), '/').'/login',
            ],
            __('auth.logout.success'),
            true,
            HttpResponse::HTTP_OK
        );
    }
}
