<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class PinMatches implements Rule
{
	/**
	 * @var User
	 */
	protected User $user;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value): bool
	{
		return $this->user->checkPin($value);
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message(): string
	{
		return 'The ' . strtoupper(':attribute') . ' is invalid.';
	}
}
