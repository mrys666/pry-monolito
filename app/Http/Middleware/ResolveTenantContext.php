<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->organization_id) {
            Context::add('organization_id', $request->user()->organization_id);
        }

        return $next($request);
    }
}
