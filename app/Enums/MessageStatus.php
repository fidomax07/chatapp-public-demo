<?php


namespace App\Enums;


use Illuminate\Support\Collection;

abstract class MessageStatus
{
	public const SENDING = 'sending';
	public const DELIVERED = 'delivered';
	public const SEEN = 'seen';

	/**
	 * Get available chat types.
	 *
	 * @return string[]
	 */
	public static function values(): array
	{
		return [
			self::SENDING,
			self::DELIVERED,
			self::SEEN
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
