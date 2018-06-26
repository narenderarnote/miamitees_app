<?php

namespace App\Http\Middleware;

use Debugbar;
use Closure;

class ApiMiddleware
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
        Debugbar::disable();
        
        return $next($request);
    }
}
