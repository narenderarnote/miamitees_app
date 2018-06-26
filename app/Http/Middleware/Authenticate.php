<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authenticate
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
        if (Auth::guard($guard)->guest()) {
            if ($request instanceof \Dingo\Api\Http\Request) {
                throw new HttpException(401, 'Unauthorized');
            }
            else {
                if ($request->ajax() || $request->wantsJson()) {
                    return response('Unauthorized.', 401);
                } else {
                    return redirect()->guest('login');
                }
            }
        }

        // ban
        if (Auth::user() && Auth::user()->isBanned()) {
            throw new HttpException(403, 'Account is banned');
        }

        return $next($request);
    }
}
