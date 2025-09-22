<?php

namespace App\Http\Controllers\Users;

use HttpResponse;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\{Request, Response, JsonResponse};
use App\Http\Resources\{UserResource, UserCollection};
use Illuminate\Database\Eloquent\ModelNotFoundException;


class UserController extends Controller
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
     * Get all users
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::all();
        return $this->responseHelper->resourceResponse(
            new UserCollection($users),
            __('user.found.collection'),
            true,
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Get the authenticated user's information
     *
     * @return JsonResponse
     */
    public function getMe(): JsonResponse
    {
        if (!auth()->check()) {
            return $this->responseHelper->errorResponse(
                __('errors.forbidden'),
                __('errors.unauthorized'),
                [],
                HttpResponse::HTTP_UNAUTHORIZED
            );
        }
            
        return $this->responseHelper->resourceResponse(
            new UserResource(auth()->user()),
            __('user.found.username', ['username' => auth()->user()->username]),
            true,
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Get a user by their ID
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ModelNotFoundException
     */
    public function findById(Request $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'errors' => [
                    'message' => 'MODEL NOT FOUND.'
                ]
            ], 404);
        }
        
        return $this->responseHelper->resourceResponse(
            new UserResource($user),
            __('user.found.id', ['id' => $request->id]),
            true,
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Get a user by their username
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function findByUsername(Request $request): JsonResponse
    {
        $user = User::where('username', $request->username)->first();
        
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => __('user.found.username', ['username' => $request->username]),
                'data' => null,
                'version' => config('app.full_name', 'Laravel Application')
            ], HttpResponse::HTTP_OK);
        }
        
        return $this->responseHelper->resourceResponse(
            new UserResource($user),
            __('user.found.username', ['username' => $request->username]),
            true,
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Get a user by their email address
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function findByEmail(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => __('user.found.email', ['email' => $request->email]),
                'data' => null,
                'version' => config('app.full_name', 'Laravel Application')
            ], HttpResponse::HTTP_OK);
        }
        
        return $this->responseHelper->resourceResponse(
            new UserResource($user),
            __('user.found.email', ['email' => $request->email]),
            true,
            HttpResponse::HTTP_OK
        );
    }
}