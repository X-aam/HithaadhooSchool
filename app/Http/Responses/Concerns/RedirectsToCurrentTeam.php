<?php

namespace App\Http\Responses\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Where a user lands once they have authenticated.
 *
 * Staff sign in to run the school website, so they land in the CMS. The
 * team-scoped dashboard this used to redirect to is a starter-kit leftover —
 * it put people on a URL built from a generated team slug, which is neither
 * memorable nor where they wanted to go.
 */
trait RedirectsToCurrentTeam
{
    protected function redirectPathForCurrentTeam(Request $request, string $redirect): string
    {
        $user = $request->user();

        abort_if(! $user, 403);

        /*
         * Team-scoped routes resolve {current_team} from this default. A user
         * with no team is fine — they simply never visit those routes — so
         * this must not abort, or an account created outside the team flow
         * could not sign in at all.
         */
        $team = $user->currentTeam ?? $user->personalTeam();

        if ($team) {
            URL::defaults(['current_team' => $team->slug]);
        }

        return $redirect;
    }
}
