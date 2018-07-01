<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
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
        if (Auth::guard($guard)->check()) {
           //return redirect('/home'); //源码是注释的这句，然而没有路由，所以改成下面的了
            session()->flash('info', '您已登录，无需再次操作。');
            return redirect('/');
        }

        return $next($request);
    }
}
