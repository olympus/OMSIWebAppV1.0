<?php

public function render($request, Throwable $exception)
{
    if ($request->wantsJson()) {
        return response()->json([
            'message' => $exception->getMessage(),
            'status_code' => method_exists($exception, 'getStatusCode') 
                ? $exception->getStatusCode() 
                : 500
        ], method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500);
    }

    return parent::render($request, $exception);
}
