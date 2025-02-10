<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SocialAccount
 *
 * @property int $id
 * @property int $user_id
 * @property string $social_id
 * @property string $social_provider
 * @property string $social_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount whereSocialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount whereSocialName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount whereSocialProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAccount whereUserId($value)
 * @mixin \Eloquent
 */
class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'social_id',
        'social_provider',
        'social_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
