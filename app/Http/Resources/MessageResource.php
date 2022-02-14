<?php

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		/** @var Message|MessageResource $this */
		return [
			'id' => $this->id,
			'chat_id' => $this->chat_id,
			'sender_id' => $this->sender_id,
			'sender_name' => $this->whenLoaded('sender', fn() => $this->sender->full_name),
			'type' => $this->type,
			'text' => $this->text,
			'pictures' => $this->when(
				$this->isPicture(),
				fn() => new MessagePictureCollection($this->pictures)),
			'status' => $this->status,
			'sending_at' => toDateTimeString($this->sending_at),
			'delivered_at' => toDateTimeString($this->delivered_at),
			'seen_at' => toDateTimeString($this->seen_at),
			'created_at' => toDateTimeString($this->created_at)
		];
	}
}
