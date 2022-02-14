<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		/** @var User|ContactResource $this */
		return [
			'id' => $this->id,
			'phone' => $this->phone,
			'username' => $this->username,
			'full_name' => $this->full_name,
			'avatar' => $this->getFirstMediaUrl('avatar')
		];
	}
}
