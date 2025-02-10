<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class GoogleController extends Controller
{
    public function loginUrl()
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function loginCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        //print_r($googleUser);
        //return 1;

        $user = null;
        $type = 'register';

        DB::transaction(function () use ($googleUser, &$user) {
            $socialAccount = SocialAccount::firstOrNew(
                ['social_id' => $googleUser->getId(), 'social_provider' => 'google'],
                ['social_name' => $googleUser->getName()]
            );

            if (!($user = $socialAccount->user)) {
                $user = User::firstOrCreate([
                    'email' => $googleUser->getEmail(),
                ],
                [
                    'password' => Hash::make(Str::random(20)),
                    'name' => $googleUser->getName(),
                    'profile_photo_url' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
                $socialAccount->fill(['user_id' => $user->id])->save();
            }
        });

        return response()->json([
            'status' => 'success',
            'type' => $type,
            'user' => new UserResource($user),
            'token' => $user->createToken('google-login')->plainTextToken,
            'google_user' => $googleUser,
        ]);
    }
}
