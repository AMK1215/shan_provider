<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headerKey = $request->header('X-Transaction-Key');

        if ($headerKey !== 'yYpfrVcWmkwxWx7um0TErYHj4YcHOOWr') {
            return response()->json(['error' => 'Unauthorized. Invalid header key.'], 401);
        }

        return $response;
    }
}
