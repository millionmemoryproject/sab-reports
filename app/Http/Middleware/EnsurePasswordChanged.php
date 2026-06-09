<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Force users flagged with must_change_password to set a new password
     * before they can use the rest of the app.
     */
    private const ALLOWED_ROUTES = [
        'profile.edit',
        'profile.update',
        'profile.password',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password
            && ! in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
            return redirect()->route('profile.edit')
                ->with('status', 'Please set a new password before continuing.');
        }

        return $next($request);
    }
}
