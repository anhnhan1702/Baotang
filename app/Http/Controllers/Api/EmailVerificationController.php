<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignedEmailVerificationRequest;
use Illuminate\Http\Request;


class EmailVerificationController extends Controller
{
    public function verify(SignedEmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect(config('saas.app_url') . '/dashboard/onboarding');
    }

    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
 
        return response()->json([
            'status' => 'success',
            'message' => 'Verification link sent!'
        ]);
    }
}
