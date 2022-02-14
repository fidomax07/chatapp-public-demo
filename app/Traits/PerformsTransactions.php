<?php


namespace App\Traits;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

trait PerformsTransactions
{
	/**
	 * @param callable $callback
	 * @return Model|Collection|mixed
	 * @throws \Throwable
	 */
	public static function performTransaction(callable $callback)
	{
		DB::beginTransaction();

		try {
			$result = $callback();
		} catch (\Exception $exception) {
			DB::rollBack();
			throw $exception;
		}

		DB::commit();

		return $result;
	}
}