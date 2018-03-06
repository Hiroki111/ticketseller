<?php

namespace App\Http\Controllers;

use App\Invitation;

class InvitationsController extends Controller
{
    public function show($code)
    {
        $invitation = Invitation::findByCode($code);

        abort_if($invitation->hasBeenUsed(), 404);

        return view('invitations.show', [
            'invitation' => $invitation,
        ]);
    }
}
