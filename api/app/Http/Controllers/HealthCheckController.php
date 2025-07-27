<?php

namespace App\Http\Controllers;

use HttpResponse;
use ResponseHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/*
|--------------------------------------------------------------------------
| Health Check Controller
|--------------------------------------------------------------------------
|
| The HealthCheckController class provides a method to handle health check
| requests to ensure the API is running properly.
|
*/

class HealthCheckController extends Controller
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
     * HealthCheckController handles the health check requests to ensure the API is running properly.
     * 
     * @var ResponseHelper
     */
    public function index(): JsonResponse
    {
        return $this->responseHelper->healthCheckResponse();
    }
}