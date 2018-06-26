<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreApiMiddleware
{
 
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = auth()->user()->token();
        if (!$token || !$token->store) {
            throw new HttpException(403, trans('messages.no_permission_to_access'));
        }
        
        return $next($request);
    }
}
