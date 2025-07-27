<?php

namespace App\Http\Responses;

use HttpResponse;
use ResponseHelper;
use Laravel\Fortify\Fortify;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\RecoveryCodesGeneratedResponse as RecoveryCodesGeneratedResponseContract;


class RecoveryCodesGeneratedResponse implements RecoveryCodesGeneratedResponseContract
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
     * @return JsonResponse|Response The response.
     */
    public function toResponse($request): JsonResponse|Response
    {
        return $request->wantsJson()
            ? $this->responseHelper->requestResponse(
                [],
                __('auth.recovery_codes.generated'),
                true,
                HttpResponse::HTTP_OK
            )
            : back()->with('status', Fortify::RECOVERY_CODES_GENERATED);
    }
}
