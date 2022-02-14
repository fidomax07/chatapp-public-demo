<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatUserResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		/** @var User|ChatUserResource $this */
		return [
			'id' => $this->id,
			'username' => $this->username,
			'full_name' => $this->full_name,
			'avatar' => $this->getFirstMediaUrl('avatar'),
		];
	}
}
