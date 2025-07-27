<?php

namespace App\Http\Responses;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\{Request, Response, JsonResponse};
use Laravel\Fortify\Contracts\FailedPasswordConfirmationResponse as FailedPasswordConfirmationResponseContract;

class FailedPasswordConfirmationResponse implements FailedPasswordConfirmationResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response|JsonResponse
     */
    public function toResponse($request): JsonResponse|Response
    {
        $message = __('auth.confirm_password.fail');

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'password' => [$message],
            ]);
        }

        return back()->withErrors(['password' => $message]);
    }
}
