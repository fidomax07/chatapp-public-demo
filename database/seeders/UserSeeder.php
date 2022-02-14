<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 * @throws \Throwable
	 */
	public function run()
	{
		User::factory()->create(['phone' => '+38349549692']);
		User::factory()->create(['phone' => '+38349200200']);
		User::factory()->create(['phone' => '+38349300300']);
		User::factory()->create(['phone' => '+38349400400']);
		User::factory()->create(['phone' => '+38349500500']);
		User::factory()->create(['phone' => '+38349600600']);
		User::factory()->create(['phone' => '+38349700700']);
		User::factory()->create(['phone' => '+38349800800']);
		User::factory()->create(['phone' => '+38349900900']);
		User::factory()->count(1)->create();
	}
}
