<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class NotificationResource
 *
 * @property int id
 * @property array data
 * @property string type
 * @property int notifiable_id
 * @property \Illuminate\Support\Carbon|null read_at
 * @property \Illuminate\Support\Carbon created_at
 */
class NotificationResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request): array
	{
		return [
			'id' => $this->id,
			// 'notifiable_id' => $this->notifiable_id,
			'type' => $this->simpleType(),
			'data' => $this->data,
			'read' => !is_null($this->read_at),
			'created_at' => toDateTimeString($this->created_at)
		];
	}

	private function simpleType()
	{
		return substr($this->type, strrpos($this->type, '\\') + 1);
	}
}
