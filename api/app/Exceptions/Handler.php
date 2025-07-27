<?php

namespace App\Exceptions;

use Throwable;
use Exception;
use ResponseHelper;
use Psr\Log\LogLevel;
use Illuminate\Http\Request;
use App\Exceptions\ModelNotDefined;
use App\Exceptions\ValidationErrorException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
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
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Report or log an exception.
     *
     * @param Throwable $exception
     * @return void
     *
     * @throws Exception|Throwable
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Throwable $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception): Response
    {
        if ($request->expectsJson()) {
            if (str_contains($exception->getMessage(), 'No query results for model')) {
                return $this->responseHelper->errorResponse(
                    __('errors.model.not_found.title'),
                    __('errors.model.not_found.message'),
                    [],
                    404
                );
            }

            if ($exception instanceof ModelNotFoundException) {
                return $this->responseHelper->errorResponse(
                    __('errors.model.not_found.title'),
                    __('errors.model.not_found.message'),
                    [],
                    404
                );
            }

            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $previous = $exception->getPrevious();
                
                if ($previous instanceof ModelNotFoundException) {
                    return $this->responseHelper->errorResponse(
                        __('errors.model.not_found.title'),
                        __('errors.model.not_found.message'),
                        [],
                        404
                    );
                }
                
                if (str_contains($exception->getMessage(), 'No query results for model')) {
                    return $this->responseHelper->errorResponse(
                        __('errors.model.not_found.title'),
                        __('errors.model.not_found.message'),
                        [],
                        404
                    );
                }
            }

            if ($exception instanceof AuthorizationException) {
                return $this->responseHelper->errorResponse(
                    __('errors.authorization.title'),
                    __('errors.authorization.message'),
                    [],
                    403
                );
            }

            if ($exception instanceof ModelNotDefined) {
                return $this->responseHelper->errorResponse(
                    __('errors.model.not_defined.title'),
                    __('errors.model.not_defined.message'),
                    [],
                    500
                );
            }
        }

        if ($exception instanceof ValidationException) {
            throw new ValidationErrorException(json_encode($exception->errors()));
        }

        return parent::render($request, $exception);
    }
}
