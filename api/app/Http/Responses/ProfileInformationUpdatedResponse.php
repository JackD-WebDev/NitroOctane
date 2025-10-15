<?php

namespace App\Http\Responses;

use ResponseHelper;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract;

class ProfileInformationUpdatedResponse implements ProfileInformationUpdatedResponseContract
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
                __('auth.profile_information_updated.success'),
                true,
                JsonResponse::HTTP_OK
            )
            : back()->with('status', Fortify::PROFILE_INFORMATION_UPDATED);
    }
}
