<?php

namespace App\Http\Controllers\Users;

use DB;
use Hash;
use Carbon;
use AgentHelper;
use HttpResponse;
use ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\{Request, Response, JsonResponse};

/*
|----------------------------------------------------------------------
| Other Browser Sessions Controller
|----------------------------------------------------------------------
|
| This controller handles the management of other browser sessions for
| authenticated users. It allows users to view their active sessions
| and log out of other browser sessions securely.
|
| It uses the ResponseHelper for standardized JSON responses.
|
| The controller requires the session driver to be set to 'database'
| for session management operations.
|
*/
class OtherBrowserSessionsController extends Controller
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
     * Retrieves the active browser sessions for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSessions(Request $request): JsonResponse
    {
        if (config('session.driver') !== 'database') {
            return $this->responseHelper->errorResponse(
                __('auth.browser_sessions.session_error.title'),
                __('auth.browser_sessions.session_error.not_configured'),
                [],
                HttpResponse::HTTP_NOT_IMPLEMENTED
            );
        }

        $data = collect(
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(
            function ($session) use ($request) {
                $userAgent = $session->user_agent;
                $parsed = AgentHelper::parseUserAgent($userAgent);
                return [
                    'user_agent' => $userAgent,
                    'browser' => $parsed['browser'] ?? 'Unknown',
                    'platform' => $parsed['platform'] ?? 'Unknown',
                    'ip' => $session->ip_address,
                    'isCurrentDevice' => $session->id === $request->session()->getId(),
                    'lastActive' => is_numeric($session->last_activity) && $session->last_activity > 0
                        ? Carbon::createFromTimestamp($session->last_activity)->diffForHumans()
                        : null,
                ];
            }
        );

        return $this->responseHelper->requestResponse(
            ['data' => $data[0] ?? []],
            __('auth.browser_sessions.retrieved'),
            true,
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Logs out other browser sessions for the authenticated user.
     *
     * @param Request $request
     * @param StatefulGuard $guard
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function logoutOtherBrowserSessions(Request $request, StatefulGuard $guard): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();
        $provider = $guard->getProvider();
        $userFromProvider = $provider->retrieveById($user->getAuthIdentifier());
        $hashedPassword = $userFromProvider ? $userFromProvider->getAuthPassword() : null;

        if (! $hashedPassword || ! Hash::check($request->password, $hashedPassword)) {
            throw ValidationException::withMessages(
                [
                    'password' => [__('auth.failed')],
                ]
            )->errorBag('logoutOtherBrowserSessions');
        }

        try {
            $guard->logoutOtherDevices($request->password);
        } catch (\Exception $e) {
            return $this->responseHelper->errorResponse(
                __('auth.browser_sessions.logout.error.title'),
                __('auth.browser_sessions.logout.error.message'),
                [],
                HttpResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $this->deleteOtherSessionRecords($request);

        return $this->responseHelper->requestResponse(
            [],
            __('auth.browser_sessions.logout.success'),
            true,
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Deletes other session records from the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    private function deleteOtherSessionRecords(Request $request): JsonResponse
    {
        if (config('session.driver') !== 'database') {
            return $this->responseHelper->errorResponse(
                __('auth.browser_sessions.session_error.not_configured'),
                __('auth.browser_sessions.session_error.title'),
                [],
                HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        return $this->responseHelper->requestResponse(
            [],
            __('auth.browser_sessions.logout.success'),
            true,
            HttpResponse::HTTP_OK
        );
    }
}
