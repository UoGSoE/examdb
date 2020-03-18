<?php

namespace App\Exceptions;

use Throwable;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Sentry\State\Scope;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            if (auth()->check()) {
                \Sentry\configureScope(function (Scope $scope): void {
                    $scope->setUser([
                        'id' => auth()->user()->id,
                        'username' => auth()->user()->username,
                    ]);
                });
            } else {
                \Sentry\configureScope(function (Scope $scope): void {
                    $scope->setUser([
                        'id' => null,
                    ]);
                });
            }
            app('sentry')->captureException($exception);
        }

        parent::report($exception);

        if ($exception instanceof \Illuminate\Routing\Exceptions\InvalidSignatureException) {
            activity()->log(
                'External tried to use a expired or invalid login url from IP '.request()->ip()
            );
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
