<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\ChangeEmailRequest;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;
use App\Models\Availability;
use App\Models\DiaBan;
use App\Models\UserProfile;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Image;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{
    public function index() {
        $users = User::where('id', '>', 1)->paginate(50);
        return UserResource::collection($users);
    }

    public function me()
    {
        if(auth()->check()) {
            $user = auth()->user();
            return new UserResource($user);
        }

        return response()->json([
            'status' => 'not_logged_in',
            'message' => 'Not logged in',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
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
        }
        // /Join team

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'team_id' => ($request->token && $request->email) ? $teamInvitation->team_id : null,
        ]);

        if($user) {
            // Join team
            if($request->token && $request->email) {
                $teamInvitation->user_id = $user->id;
                $teamInvitation->save();
            }

            UserProfile::create([
                'user_id' => $user->id,
            ]);

            event(new Registered($user));

            return response()->json([
                'status' => 'success',
                'token' => $user->createToken('web')->plainTextToken
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Oops! Something went wrong. Please try again later.',
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        if($request->name) {
            $user->name = $request->name;
        }
  
        if($request->profile_photo_url) {
            if($request->profile_photo_url == 'remove') { 
                $user->profile_photo_url = null;
            } else {
                if($request->hasFile('profile_photo_url')) {
                    $year = Carbon::now()->format('Y');
                    $month = Carbon::now()->format('m');
                    $fileExtension = $request->profile_photo_url->getClientOriginalExtension();
                    $prefixPath = config('saas.env') === 'production' ? '' : config('saas.env').'/';
                    $fileName = $this->generateFileName("{$prefixPath}profile-photos/{$year}/{$month}/", $fileExtension);
                    $fileUrl = "{$prefixPath}profile-photos/{$year}/{$month}/".$fileName;
                    $optimizedFile = Image::make($request->profile_photo_url)
                        ->fit(100, 100, function ($constraint) {
                            $constraint->upsize();
                        })->stream($fileExtension, 100)->__toString();
                    $upload = Storage::put($fileUrl, $optimizedFile, ['visibility' => 'public', 'mimetype' => 'image/'.$fileExtension]);
                    if($upload) {
                        $user->profile_photo_url = Storage::url($fileUrl);
                    }
                }
            }
        }
        
        $changePassword = false;
        // if($request->old_password && $request->new_password && $request->new_password_confirmation) {
        if($request->old_password && $request->new_password) {
            if(!$user->socialAccounts()->count()) {
                if (! Hash::check($request->old_password, $user->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Wrong old password. Please try again.'
                    ]);
                } 
            }
            // if($request->new_password != $request->new_password_confirmation) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Repeat passwords do not match.'
            //     ]);
            // }
            $user->password = Hash::make($request->new_password);
            $changePassword = true;
        }

        // Brand kit
        if($request->brandkit_logo_url) {
            if($request->brandkit_logo_url == 'remove') { 
                $user->profile->brandkit_logo_url = null;
            } else {
                if($request->hasFile('brandkit_logo_url')) {
                    $year = Carbon::now()->format('Y');
                    $month = Carbon::now()->format('m');
                    $fileExtension = $request->brandkit_logo_url->getClientOriginalExtension();
                    $prefixPath = config('saas.env') === 'production' ? '' : config('saas.env').'/';
                    $fileName = $this->generateFileName("{$prefixPath}brandkit-logos/{$year}/{$month}/", $fileExtension);
                    $fileUrl = "{$prefixPath}brandkit-logos/{$year}/{$month}/".$fileName;
                    $optimizedFile = Image::make($request->brandkit_logo_url)
                        ->fit(100, 100, function ($constraint) {
                            $constraint->upsize();
                        })->stream($fileExtension, 100)->__toString();
                    $upload = Storage::put($fileUrl, $optimizedFile, ['visibility' => 'public', 'mimetype' => 'image/'.$fileExtension]);
                    if($upload) {
                        $user->profile->brandkit_logo_url = Storage::url($fileUrl);
                    }
                }
            }
        }
        if($request->assembly_token && !$user->profile->assembly_token) {
            $user->profile->assembly_token = $request->assembly_token;
        }

        if($user->save() && $user->profile->save()) {
            if($changePassword) {
                // Send Email:25:
                //$user->notify(new GeneralNotification('Email:25:', 'Email:25:Content'));
            }

            return response()->json([
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Oops! Something went wrong. Please try again later.',
        ]);
    }

    protected function generateFileName($path, $extension)
    {
        $fileName = Str::random(30);
        $fullFileName = $fileName.'.'.$extension;
        $filePath = $path.$fullFileName;
        while(Storage::exists($filePath)) {
            $fileName = Str::random(10);
            $fullFileName = $fileName.'.'.$extension;
            $filePath = $path.$fullFileName;
        }
        return $fullFileName;
    }

    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        if($request->email != $user->email) {
            $duplicateUser = User::where('email', $request->email)
                ->select(['email'])->first();

            if($duplicateUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This email is already taken. Please try another one.',
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function changeEmailRequest(Request $request)
    {
        $user = auth()->user();
        
        $old_email_otp = rand(100000, 999999);
        $user->old_email_verify_otp = md5($old_email_otp);
        $otp = rand(100000, 999999);
        $user->email_verify_otp = md5($otp);
        
        if($user->save()) {
            Mail::to($user)->queue(new ChangeEmailRequest($old_email_otp, $user->name));
            $user->email = $request->new_email;
            Mail::to($user)->queue(new ChangeEmailRequest($otp, $user->name));
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function changeEmailConfirm(Request $request)
    {
        $user = auth()->user();

        if(md5($request->otp) != $user->email_verify_otp || md5($request->old_email_otp) != $user->old_email_verify_otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wrong OTP, please try again.',
            ]);
        }

        $user->email = $request->email;

        if($user->save()) {
            // Send Email:7:
            $user->notify(new GeneralNotification('Email:7:', 'Email:7:Content'));

            return response()->json([
                'status' => 'success',
                'message' => 'Email changed successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Oops! Something went wrong. Please try again later.',
        ]);
    }

    public function tree()
    {
        $diabans = DiaBan::whereNull('parent_id')->get();
        $tree = [];
        foreach($diabans as $diaban) {
            $toChucs = [];
            foreach($diaban->to_chucs as $toChuc) {
                $toChucs[] = [
                    'id' => $toChuc->id,
                    'name' => $toChuc->name,
                    'users' => $toChuc->users,
                ];
            }

            $children = [];
            // foreach($diaban->children as $childDiaban) {
            //     $childToChucs = [];
            //     foreach($childDiaban->to_chucs as $childToChuc) {
            //         $childToChuc[] = [
            //             'name' => $childToChuc->name,
            //             'users' => $childToChuc->users,
            //         ];
            //     }

            //     $children[] = [
            //         'name' => $diaban->name,
            //         'to_chucs' => $childToChucs,
            //     ];
            // }

            $tree[] = [
                'id' => $diaban->id,
                'name' => $diaban->name,
                'to_chucs' => $toChucs,
                'children' => $children,
            ];
        }
        return $tree;
    }
}
