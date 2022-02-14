<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = User::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition(): array
	{
		return [
			'hash_id' => \Str::random(20),
			'phone' => $this->faker->unique()->e164PhoneNumber,
			'pin' => '$2y$12$zXKRGGLBHMVtKBn7cFTIuOyAWNuDwffuhte0T7cJpB5A5JkjbMK1y', // 097600
			'username' => preg_replace(
				"/[^A-Za-z0-9 ]/",
				'',
				($this->faker->unique()->userName.$this->faker->unique()->userName)
			),
			'verified_at' => now(),

			'first_name' => $this->faker->firstName,
			'last_name' => $this->faker->lastName,

			'ntf_enabled' => true,
		];
	}

	/**
	 * Indicate that the model's email address should be unverified.
	 *
	 * @return \Illuminate\Database\Eloquent\Factories\Factory
	 */
	public function unverified(): Factory
	{
		return $this->state(function (/*array $attributes*/) {
			return [
				'verified_at' => null,
			];
		});
	}
}
