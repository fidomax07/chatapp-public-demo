<?php

namespace App\Http\Resources;

use App\Models\Chat;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		/** @var Chat|ChatResource $this */
		return [
			'id' => $this->id,
			'name' => $this->name,
			'created_at' => toDateTimeString($this->created_at),
			'updated_at' => toDateTimeString($this->updated_at),
			'users' => ChatUserResource::collection($this->whenLoaded('users')),
			'messages_count' => $this->messages_count,
			'messages' => MessageResource::collection($this->whenLoaded('messages'))
		];
	}
}
