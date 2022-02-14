<?php


namespace App\Traits;


use Hashids\Hashids;

trait HashesIntegers
{
	/**
	 * @var string
	 */
	protected $alphabet = '123456789abcdefghijklmnopqrstuvwxyz';


	/**
	 * @param int $value
	 * @param int $minLength
	 * @return string
	 */
	public function hashInt(int $value, int $minLength = 6): string
	{
		return $this->getHash($minLength)->encode($value);
	}

	/**
	 * @param int $minHashLength
	 * @return Hashids
	 */
	private function getHash(int $minHashLength): Hashids
	{
		return new Hashids(
			config('hashids.salt'),
			$minHashLength,
			$this->alphabet
		);
	}


	/**
	 * @param $value
	 * @return HashesIntegers
	 */
	protected function setAlphabet($value): HashesIntegers
	{
		$this->alphabet = $value;
		return $this;
	}
}
