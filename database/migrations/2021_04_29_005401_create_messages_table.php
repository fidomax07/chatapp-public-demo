<?php

use App\Enums\MessageType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('messages', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->foreignId('chat_id')->constrained()->cascadeOnDelete();
			$table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
			$table->enum('type', MessageType::values())->default(MessageType::TEXT);
			$table->text('text')->nullable();
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
		Schema::dropIfExists('messages');
	}
}
