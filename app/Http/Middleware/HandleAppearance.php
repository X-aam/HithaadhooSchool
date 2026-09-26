<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /*
         * Dark mode is a CMS preference. The public site has no switch and is
         * always rendered light, even for staff who chose dark in the CMS.
         * Keep this path list in sync with isCmsPath() in useAppearance.ts.
         */
        $isCms = $request->is('admin', 'admin/*', 'settings', 'settings/*', 'teams', 'teams/*', '*/dashboard');

        View::share('appearance', $isCms ? ($request->cookie('appearance') ?? 'light') : 'light');

        return $next($request);
    }
}
