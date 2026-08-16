<?php

namespace Remotelywork\Installer\Http\Middleware;

use Closure;
use Remotelywork\Installer\Repository\App;

class ValidateLicense
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
