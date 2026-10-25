<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user() && DB::table('rbac_userroles')->where('UserID', $request->user()->id)->exists(), 403, 'An administrator must assign an admin role to this account.');

        return $next($request);
    }
}
