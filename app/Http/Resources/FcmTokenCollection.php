<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FcmTokenCollection extends ResourceCollection
{
	/**
	 * @var string
	 */
	public $collects = FcmTokenResource::class;

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
