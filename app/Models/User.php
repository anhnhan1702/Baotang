<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_url',
        'email_verified_at',
        'team_id',
        'to_chuc_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAvatarUrl()
    {
        return $this->profile_photo_url ?: 'https://ui-avatars.com/api/?name='.$this->name;
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function to_chuc()
    {
        return $this->belongsTo(ToChuc::class);
    }

    public function childUserIds()
    {
        $userIds = [$this->id];
        if($this->to_chuc_id) {
            $toChuc = ToChuc::find($this->to_chuc_id);
            $diaBan = DiaBan::find($toChuc->id);
            foreach($diaBan->children as $childDiaBan) {
                $childToChuc = ToChuc::where('dia_ban_id', $childDiaBan->id)->first();
                if($childToChuc) {
                    $findUserIds = User::where('to_chuc_id', $childToChuc->id)->get()->pluck(['id'])->toArray();
                    $userIds = array_merge($userIds, $findUserIds);
                
                    foreach($childDiaBan->children as $childDiaBanChild) {
                        $childToChucChild = ToChuc::where('dia_ban_id', $childDiaBanChild->id)->first();
                        if($childToChucChild) {
                            $findUserIds = User::where('to_chuc_id', $childToChuc->id)->get()->pluck(['id'])->toArray();
                            $userIds = array_merge($userIds, $findUserIds);
                        }
                    }
                }
            }
        }

        return $userIds;
    }
}
