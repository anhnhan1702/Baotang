<?php

namespace App\Http\Resources;

use App\Models\Collector;
use App\Models\LoveWidget;
use App\Models\Testimonial;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_admin' => $this->id === 1,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'profile_photo_url' => $this->profile_photo_url ?: '',
            'profile' => $this->profile,
            'social_accounts' => $this->socialAccounts,
            'speaker_labels' => [],
            'team' => $this->team,
            'to_chuc_id' => $this->to_chuc_id,
            'subscribed' => true,
            'trial_days_left' => 1000,
            'is_trial_ended' => false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
