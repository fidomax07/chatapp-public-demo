<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('hash_id')->index()->nullable();

			// Register & Login Credentials
			$table->string('phone', 20)->unique(); // sanitized
			$table->string('pin'); // bcrypted
			$table->string('username', 45)->unique()->nullable();
			$table->string('sms_vc')->nullable(); // encrypted
			$table->timestamp('sms_vc_generated_at')->nullable();
			$table->unsignedSmallInteger('sms_vc_attempts')->default(0);
			$table->unsignedSmallInteger('sms_vc_sents')->default(0);
			$table->timestamp('verified_at')->nullable();

			// User's data
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();

			// User's settings
			$table->boolean('ntf_enabled')->default(true);

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}
}
