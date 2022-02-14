<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$users = User::all()->skip(1);

		User::first()->contacts()->attach($users->take(3)->pluck('id'));

		$users->first()->contacts()->attach([1, 3, 4]);
		$users->skip(1)->first()->contacts()->attach([1, 2, 5]);
	}
}
