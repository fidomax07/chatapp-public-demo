<?php

namespace App\Http\Resources;

use App\Models\MessagePicture;
use Illuminate\Http\Resources\Json\JsonResource;

class MessagePictureResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		/** @var MessagePicture|MessagePictureResource $this */
		$picture = $this->picture();
		return [
			'url' => $picture->getUrl(),
			'mime_type' => $picture->mime_type,
			'created_at' => toDateTimeString($this->created_at)
		];
	}
}
