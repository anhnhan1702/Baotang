<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SanctumController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);
     
        $user = User::where('email', $request->email)->first();
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect login details. Please try again.'
            ]);
        }

        // Join team
        if($request->token && $request->email) {
            $teamInvitation = TeamInvitation::where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            if (!$teamInvitation) {
                return response()->json([
                    'status' => 'team_not_found',
                    'message' => 'Team not found.',
                ]);
            }

            if ($teamInvitation->email != $request->email) {
                return response()->json([
                    'status' => 'no_permission',
                    'message' => 'You have no permission to join this team.'
                ]);
            }

            $teamInvitation->status = 'active';
            $teamInvitation->token = null;
            $teamInvitation->user_id = $user->id;
            $teamInvitation->save();
            $user->team_id = $teamInvitation->team_id;
            $user->save();
        }
        // /Join team
     
        return response()->json([
            'status' => 'success',
            'token' => $user->createToken($request->device_name)->plainTextToken
        ]);
    }
}
