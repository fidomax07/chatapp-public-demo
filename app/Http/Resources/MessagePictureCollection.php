<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MessagePictureCollection extends ResourceCollection
{
	public $collects = MessagePictureResource::class;

	/**
	 * Transform the resource collection into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		return parent::toArray($request);
	}
}
