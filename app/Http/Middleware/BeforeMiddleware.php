<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
 
class BeforeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Perform action
        // Check if the request is a login POST request
        if ($request->isMethod('post')) {
            return response()->json([
                'message' => 'CSRF token should be valid',
                'csrf_token' => $request->input('_token'),
                'headers' => $request->headers->all(),
                'request_data' => $request->all()
            ], 422);
        }
        return $next($request);
    }
}