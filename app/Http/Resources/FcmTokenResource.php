<?php

namespace App\Http\Resources;

use App\Models\FcmToken;
use Illuminate\Http\Resources\Json\JsonResource;

class FcmTokenResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		/** @var FcmToken $this */
		return [
			'id' => $this->id,
			'value' => $this->value,
			'device_name' => $this->device_name,
			'created_at' => toDateTimeString($this->created_at)
		];
	}
}
