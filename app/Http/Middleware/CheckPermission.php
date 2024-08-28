<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!$request->user() || !$request->user()->role || !in_array($permission, $request->user()->role->permissions ?? [])) {
            $request->session()->flash('error', 'Unauthorized action.');

            return redirect()->to('lobby');
        }

        return $next($request);
    }
}
