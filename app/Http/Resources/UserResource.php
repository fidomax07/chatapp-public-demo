<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		/** @var User|UserResource $this */
		return [
			'id' => $this->id,
			//'hash_id' => $this->hash_id,
			'phone' => $this->phone,
			'username' => $this->username,
			'verified_at' => toDateTimeString($this->verified_at),
			'first_name' => $this->first_name,
			'last_name' => $this->last_name,
			'avatar' => $this->getFirstMediaUrl('avatar'),
			'ntf_enabled' => $this->ntf_enabled,
			'created_at' => toDateTimeString($this->created_at),
			'updated_at' => toDateTimeString($this->updated_at),
			// 'sms_vc' => $this->sms_vc,

			'fcm_tokens' => new FcmTokenCollection($this->whenLoaded('fcmTokens'))
		];
	}
}
