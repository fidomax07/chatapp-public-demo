<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserContactPivotTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_contact', function (Blueprint $table) {
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->foreignId('contact_id')->constrained('users')->cascadeOnDelete();

			$table->unique(['user_id', 'contact_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_contact', function (Blueprint $table) {
			$table->dropUnique(['user_id', 'contact_id']);
		});
		Schema::dropIfExists('user_contact');
	}
}
