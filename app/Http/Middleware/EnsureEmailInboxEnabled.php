<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Backs the Email Inbox feature flag (Settings > Email Inbox >
 * "Email Inbox System"). Applied to every admin email-inbox route so
 * turning the flag off actually disables the feature -- not just hides
 * the sidebar link.
 */
class EnsureEmailInboxEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (! setting('email_inbox_enabled', 'email_inbox')) {
            abort(404);
        }

        return $next($request);
    }
}
