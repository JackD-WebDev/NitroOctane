<?php

namespace App\Http\Helpers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;


/*
|--------------------------------------------------------------------------
| Response Helper
|--------------------------------------------------------------------------
|
| The ResponseHelper class provides methods to create standardized JSON
| responses for successful and error cases in the application.
|
*/

class ResponseHelper
{
    /**
     * Create a success response.
     *
     * @param mixed $data The data to be included in the response (array or Model).
     * @param string $message The response message.
     * @param bool $success The success status of the response (default: true).
     * @param int $code The response code (default: 200).
     * @return JsonResponse The JSON response.
     */
    public function requestResponse(
        $data = [],
        ?string $message = null,
        bool $success = true,
        int $code = Response::HTTP_OK
    ): JsonResponse
    {
        if ($data instanceof Model) {
            $data = $data->toArray();
        } else if (!is_array($data)) {
            $data = method_exists($data, 'toArray') ? $data->toArray() : (array) $data;
        }

        return $this->createResponse($data, $message, $success, $code);
    }

    /**
     * Create a resource response.
     *
     * @param string $message The response message.
     * @param int $code The response code (default: 200).
     * @param bool $success The success status of the response (default: true).
     * @param JsonResource|null $data The data to be included in the response.
     * @return JsonResponse The JSON response.
     */
    public function resourceResponse(
        JsonResource $data,
        ?string $message = null,
        bool $success = true,
        int $code = Response::HTTP_OK
    ): JsonResponse
    {
        $message = $message ?? __('default.success');

        $response = [
            'data' => $data->response()->getData(true)['data'],
            'links' => $data->response()->getData(true)['links'],
            'meta' => $data->response()->getData(true)['meta'],
            'version' => config('app.full_name', 'Laravel Application'),
        ];

        return $this->createResponse($response, $message, $success, $code);
    }
    
    /**
     * Generate an error response.
     *
     * @param string $message The error message. Default is 'generic'.
     * @param int $code The HTTP status code. Default is 500 (Internal Server Error).
     * @param string $title The error title. Default is 'error'.
     * @param array $errors Additional error details. Default is an empty array.
     * @return JsonResponse The error response.
     */
    public function errorResponse(
        ?string $title = null,
        ?string $message = null,
        array $errors = [],
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $headers = []
    ): JsonResponse
    {
        $title = $title ?? __('error.generic.title');
        $message = $message ?? __('errors.generic.message');
        $debug = null;
        if (array_key_exists('_debug', $errors)) {
            $debug = $errors['_debug'];
            unset($errors['_debug']);
        }

        $responseData = [
            'errors' => [
                'title' => strtoupper($title),
                'details' => $errors,
            ]
        ];

        if ($debug !== null) {
            $responseData['_debug'] = $debug;
        }

        return $this->createResponse($responseData, $message, false, $code, $headers);
    }

    /**
     * Generate a health check response.
     * This method creates a JSON response indicating the health status of the application.
     *
     * @return JsonResponse The JSON response containing the health check status.
     */
    public function healthCheckResponse(): JsonResponse
    {
        $dbHealth = DB::table('health')->value('health');
        if ($dbHealth !== 1) {
            return $this->createResponse(
                [],
                __('default.health_check.failure'),
                false,
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }
        return $this->createResponse(
            [],
            __('default.health_check.success'),
            true,
            Response::HTTP_OK
        );
    }

    /**
     * Creates a JSON response with the specified message (converted to uppercase), code, success status, and data.
     *
     * @param array $data The additional data to include in the response.
     * @param string $message The message to include in the response (converted to uppercase).
     * @param bool $success The success status of the response.
     * @param int $code The HTTP status code.
     * @return JsonResponse The JSON response.
     */
    private function createResponse(array $data, ?string $message, bool $success, int $code, array $headers = []): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => strtoupper($message),
        ];

        if (!empty($data) && array_key_exists('_debug', $data)) {
            $debug = $data['_debug'];
            unset($data['_debug']);
        } else {
            $debug = null;
        }

        if (!empty($data)) {
            $response = array_merge($response, $data);
        }

        if (config('app.debug') && $debug !== null) {
            $response['debug'] = $debug;
        }

        $resp = response()->json($response, $code);

        foreach ($headers as $k => $v) {
            $resp->headers->set($k, $v);
        }

        return $resp;
    }
}
