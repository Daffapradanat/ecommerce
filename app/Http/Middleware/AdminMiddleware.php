<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Gate::allows('viewTelescope', $request->user())) {
            return $next($request);
        }
        return redirect('/home')->with('error', 'Akses tidak diizinkan.');
    }
}