<?php


namespace App\Enums;


use Illuminate\Support\Collection;

abstract class MessageType
{
	public const TEXT = 'text';
	public const PICTURE = 'picture';


	/**
	 * Get available chat types.
	 *
	 * @return string[]
	 */
	public static function values(): array
	{
		return [
			self::TEXT,
			self::PICTURE
		];
	}


	/**
	 * Get a collection of available chat types.
	 *
	 * @return Collection
	 */
	public static function toCollection(): Collection
	{
		return collect(self::values());
	}
}
