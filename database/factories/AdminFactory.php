<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Admin::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition(): array
	{
		return [
			'email' => $this->faker->unique()->safeEmail,
			'password' => '$2y$10$P19rTJohgh5.YQBVhsDU9el04HB0/LRoKYQR3peqeETzB.fUQbkCa', // secret
		];
	}
}
