<?php

use Carbon\Carbon;

if (!function_exists('toDateString')) {
	/**
	 * Get the string representation of the given date.
	 *
	 * @param $date
	 * @return string|null
	 */
	function toDateString($date): ?string
	{
		if (is_null($date)) {
			return null;
		}
		if (!$date instanceof Carbon) {
			$date = Carbon::parse($date);
		}

		return $date->translatedFormat(config('api.date_format'));
	}
}

if (!function_exists('toDateTimeString')) {
	/**
	 * Get the string representation of the given timestamp.
	 *
	 * @param $dateTime
	 * @return string|null
	 */
	function toDateTimeString($dateTime): ?string
	{
		if (is_null($dateTime)) {
			return null;
		}
		if (!$dateTime instanceof Carbon) {
			$dateTime = Carbon::parse($dateTime);
		}

		return $dateTime->translatedFormat(config('api.timestamp_format'));
	}
}

if (!function_exists('parseToYearMonthDay')) {
	/**
	 * Get the database string format of the given date.
	 *
	 * @param Carbon|string $date
	 * @return string|null
	 */
	function parseToYearMonthDay($date): ?string
	{
		if (is_null($date)) {
			return null;
		}
		if (!$date instanceof Carbon) {
			$date = Carbon::parse($date);
		}

		return $date->format('Y-m-d');
	}
}
