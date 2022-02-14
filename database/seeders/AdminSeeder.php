<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Admin::factory()->create(['email' => 'admin01@chatapp.com']);
		Admin::factory()->create(['email' => 'admin02@chatapp.com']);
	}
}
