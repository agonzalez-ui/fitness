<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Si entra al área equivocada se le lleva a la suya, en vez de mostrar un 403. */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user->role !== Role::from($role)) {
            return redirect()->route($user->role->home());
        }

        return $next($request);
    }
}
