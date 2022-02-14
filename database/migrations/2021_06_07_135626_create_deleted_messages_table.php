<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeletedMessagesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('deleted_messages', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->foreignUuid('message_id')->constrained()->cascadeOnDelete();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();

			$table->unique(['message_id', 'user_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('deleted_messages', function (Blueprint $table) {
			$table->dropUnique(['message_id', 'user_id']);
		});
		Schema::dropIfExists('deleted_messages');
	}
}
